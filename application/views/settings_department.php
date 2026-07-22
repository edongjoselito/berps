<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>
<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid settings-dept-page" style="padding-bottom:100px;">

                    <style>
                        .settings-dept-page .sd-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 24px 0 22px;
                            border-radius: 16px;
                            background: #78350f;
                            box-shadow: 0 8px 32px rgba(120, 53, 15, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .settings-dept-page .sd-hero::before {
                            content: '';
                            position: absolute;
                            top: -50%;
                            right: -10%;
                            width: 400px;
                            height: 400px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.06);
                            pointer-events: none;
                        }

                        .settings-dept-page .sd-hero::after {
                            content: '';
                            position: absolute;
                            bottom: -60%;
                            right: 15%;
                            width: 300px;
                            height: 300px;
                            border-radius: 50%;
                            background: rgba(255, 255, 255, 0.04);
                            pointer-events: none;
                        }

                        .settings-dept-page .sd-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .settings-dept-page .sd-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .settings-dept-page .sd-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .settings-dept-page .sd-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                        }

                        .settings-dept-page .sd-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 520px;
                        }

                        .settings-dept-page .sd-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .settings-dept-page .sd-hero-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            padding: 8px 16px;
                            border-radius: 10px;
                            border: 1px solid rgba(255, 255, 255, 0.3);
                            background: rgba(255, 255, 255, 0.15);
                            color: #fff;
                            font-size: 0.82rem;
                            font-weight: 600;
                            text-decoration: none;
                            cursor: pointer;
                            transition: all 0.18s ease;
                        }

                        .settings-dept-page .sd-hero-btn:hover,
                        .settings-dept-page .sd-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        .settings-dept-page .store-swing {
                            display: inline-block;
                            animation: store-swing 2.5s ease-in-out infinite;
                            transform-origin: 50% 80%;
                        }

                        @keyframes store-swing {
                            0%, 70%, 100% { transform: rotate(0deg); }
                            15% { transform: rotate(-12deg); }
                            30% { transform: rotate(10deg); }
                            45% { transform: rotate(-6deg); }
                            60% { transform: rotate(0deg); }
                        }

                        .settings-dept-page .card {
                            border-top: 3px solid #78350f;
                            border-radius: 14px;
                            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
                        }

                        .settings-dept-page .card-title {
                            color: #78350f;
                        }

                        @media (max-width: 767px) {
                            .settings-dept-page .sd-hero,
                            .settings-dept-page .sd-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .settings-dept-page .sd-hero {
                                padding: 20px;
                            }

                            .settings-dept-page .sd-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }
                    </style>

                    <?php if ($this->session->flashdata('msg')): ?>
                        <div class="row mt-3">
                            <div class="col-12">
                                <?= $this->session->flashdata('msg'); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="sd-hero">
                        <div class="sd-hero__content">
                            <div class="sd-hero__eyebrow">
                                <i class="mdi mdi-store-outline"></i>
                                Organization Setup
                            </div>
                            <h1 class="sd-hero__title">Branches &amp; Cost Centers <span class="store-swing">🏬</span></h1>
                            <p class="sd-hero__subtitle">Create and manage branches or cost centers with activation keys for multi-location operations.</p>
                        </div>
                        <div class="sd-hero__actions">
                            <a class="sd-hero-btn" href="<?= base_url(); ?>Page/admin">
                                <i class="mdi mdi-arrow-left"></i>
                                <span>Back to Dashboard</span>
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-3">Add Branch / Cost Center</h4>
                                    <form method="post">
                                        <div class="form-group">
                                            <label>Code</label>
                                            <input type="text" name="DeptCode" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="DeptName" class="form-control" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Location / Area (optional)</label>
                                            <input type="text" name="Location" class="form-control" placeholder="e.g., Davao, Online">
                                        </div>
                                        <div class="form-group">
                                            <label>Notes (optional)</label>
                                            <input type="text" name="Notes" class="form-control" placeholder="any short note">
                                        </div>
                                        <div class="form-group">
                                            <label>Activation Key</label>
                                            <input type="text" name="activation_key" class="form-control" placeholder="BR-XXXX-XXXX-XXXX" required>
                                            <small class="form-text text-muted">A branch key generated by the System Administrator is required for each new branch.</small>
                                        </div>
                                        <button type="submit" name="submit" class="btn btn-primary">Save</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title mb-3">Branches / Cost Centers</h4>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Code</th>
                                                    <th>Name</th>
                                                    <th>Location / Area</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($data)): ?>
                                                    <?php foreach ($data as $row): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($row->DeptCode ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars($row->DeptName ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars($row->Location ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td><?= htmlspecialchars($row->Notes ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="4" class="text-center text-muted">No departments yet.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include('includes/footer.php'); ?>
        </div>
    </div>

    <?php include('includes/themecustomizer.php'); ?>
    <script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
    <script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
