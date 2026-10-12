-- BERPS data cleanup: payments that did not reach their invoice, mistyped
-- payment years, and repeated "(qty unit @ PHP price)" text in descriptions.
--
-- Why: Accounting Reports > Receivables rebuilds each balance from the payments
-- table (matched by InvoiceNo, dated on/before the report date), while the
-- invoice list reads invoice.Balance. The payments below made the two disagree.
--
-- Safe to re-run: every payment UPDATE matches the row's current (wrong) values,
-- so a row that was already corrected, or does not exist, is left untouched.
-- Works on MySQL 8 and MariaDB (no REGEXP_REPLACE).

SET @old_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');

-- ---------------------------------------------------------------------------
-- 1. Payments saved under the wrong invoice number
-- ---------------------------------------------------------------------------

-- IHMA training fee: saved under #100143 (no such invoice); belongs to #2166.
UPDATE payments SET InvoiceNo = '2166'
WHERE paymentID = 6165 AND settingsID = 1 AND InvoiceNo = '100143'
  AND CustID = '20220019' AND AmountPaid = 5000;

-- VMC Asian College yearly maintenance: saved under #100167 (a deleted
-- Wesleyan invoice); belongs to VMC's #1968.
UPDATE payments SET InvoiceNo = '1968'
WHERE paymentID = 6189 AND settingsID = 1 AND InvoiceNo = '100167'
  AND CustID = '20260011' AND AmountPaid = 100000;

-- ---------------------------------------------------------------------------
-- 2. Payment dates with a mistyped year
-- ---------------------------------------------------------------------------

-- Wesleyan monthly #100194-#100198 are Aug-Dec 2025 invoices. Their payments
-- were entered in one batch (6263-6271) with the invoice's own date, but with
-- year 2026; three of them were dated in the future.
UPDATE payments SET PDate = '2025-08-15' WHERE paymentID = 6271 AND settingsID = 1 AND InvoiceNo = '100194' AND PDate = '2026-08-15';
UPDATE payments SET PDate = '2025-09-15' WHERE paymentID = 6270 AND settingsID = 1 AND InvoiceNo = '100195' AND PDate = '2026-09-15';
UPDATE payments SET PDate = '2025-10-15' WHERE paymentID = 6269 AND settingsID = 1 AND InvoiceNo = '100196' AND PDate = '2026-10-15';
UPDATE payments SET PDate = '2025-11-15' WHERE paymentID = 6268 AND settingsID = 1 AND InvoiceNo = '100197' AND PDate = '2026-11-15';
UPDATE payments SET PDate = '2025-12-15' WHERE paymentID = 6267 AND settingsID = 1 AND InvoiceNo = '100198' AND PDate = '2026-12-15';

-- IHMA #100087: year typed as 0023; neighbouring payments in the batch are Feb 2023.
UPDATE payments SET PDate = '2023-02-12' WHERE paymentID = 6176 AND settingsID = 1 AND InvoiceNo = '100087' AND PDate = '0023-02-12';

-- DepEd #1846: year typed as 0225; the rest of that batch is dated 2025-01-02.
UPDATE payments SET PDate = '2025-01-02' WHERE paymentID = 6299 AND settingsID = 1 AND InvoiceNo = '1846' AND PDate = '0225-01-02';

-- Not changed (needs a person to confirm):
--   payment 6252, Golden Link #2197, PDate 0003-04-13 - intended date unknown.
--   invoice #1737, Wesleyan yearly PHP 50,000 - marked paid but has no payment.

-- ---------------------------------------------------------------------------
-- 3. Collapse back-to-back repeats of the same "(qty unit @ PHP price)" text,
--    e.g. "... (1 month @ PHP 15,750.00) (1 month @ PHP 15,750.00)".
--    Amounts are not affected; only the description text. Each REPLACE is
--    nested three times, which collapses up to eight repeats.
-- ---------------------------------------------------------------------------

DROP TEMPORARY TABLE IF EXISTS tmp_repeated_breakdown;
CREATE TEMPORARY TABLE tmp_repeated_breakdown (token varchar(64) NOT NULL);
INSERT INTO tmp_repeated_breakdown (token) VALUES
  ('(1 each @ PHP 120,000.00)'),
  ('(1 each @ PHP 15,000.00)'),
  ('(1 each @ PHP 18,000.00)'),
  ('(1 each @ PHP 190,000.00)'),
  ('(1 each @ PHP 20,000.00)'),
  ('(1 each @ PHP 25,000.00)'),
  ('(1 each @ PHP 25,200.00)'),
  ('(1 each @ PHP 27,500.00)'),
  ('(1 each @ PHP 30,000.00)'),
  ('(1 each @ PHP 4,000.00)'),
  ('(1 each @ PHP 95,000.00)'),
  ('(1 month @ PHP 15,750.00)'),
  ('(1 month @ PHP 3,700.00)'),
  ('(1 year @ PHP 100,000.00)'),
  ('(1 year @ PHP 50,000.00)'),
  ('(1 year @ PHP 58,000.00)'),
  ('(2 each @ PHP 120,000.00)'),
  ('(3 month @ PHP 15,750.00)');

UPDATE invoice i
JOIN tmp_repeated_breakdown t ON i.JobDescription LIKE CONCAT('%', t.token, ' ', t.token, '%')
SET i.JobDescription = REPLACE(REPLACE(REPLACE(i.JobDescription,
  CONCAT(t.token, ' ', t.token), t.token),
  CONCAT(t.token, ' ', t.token), t.token),
  CONCAT(t.token, ' ', t.token), t.token);

UPDATE invoice_items ii
JOIN tmp_repeated_breakdown t ON ii.itemDescription LIKE CONCAT('%', t.token, ' ', t.token, '%')
SET ii.itemDescription = REPLACE(REPLACE(REPLACE(ii.itemDescription,
  CONCAT(t.token, ' ', t.token), t.token),
  CONCAT(t.token, ' ', t.token), t.token),
  CONCAT(t.token, ' ', t.token), t.token);

UPDATE payments p
JOIN tmp_repeated_breakdown t ON p.TransDescription LIKE CONCAT('%', t.token, ' ', t.token, '%')
SET p.TransDescription = REPLACE(REPLACE(REPLACE(p.TransDescription,
  CONCAT(t.token, ' ', t.token), t.token),
  CONCAT(t.token, ' ', t.token), t.token),
  CONCAT(t.token, ' ', t.token), t.token);

DROP TEMPORARY TABLE IF EXISTS tmp_repeated_breakdown;

SET SESSION sql_mode = @old_sql_mode;
