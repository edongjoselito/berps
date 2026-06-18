<?php
$noteRows = isset($data) ? $data : array();
if ($noteRows instanceof Traversable) {
    $noteRows = iterator_to_array($noteRows, false);
}
$noteRows = is_array($noteRows) ? array_values($noteRows) : array();

$notes = array();
$uniqueTagsMap = array();
$favoritesCount = 0;
$linkedNotesCount = 0;

foreach ($noteRows as $row) {
    $noteId = isset($row->noteID) ? (int) $row->noteID : 0;
    $titleRaw = trim((string) (isset($row->title) ? $row->title : ''));
    $displayTitle = $titleRaw !== '' ? $titleRaw : 'Untitled Note';
    $descriptionSource = (string) (isset($row->noteDescription) ? $row->noteDescription : '');
    $plainText = trim(html_entity_decode(strip_tags($descriptionSource), ENT_QUOTES, 'UTF-8'));

    if ($descriptionSource === '') {
        $renderHtml = '';
    } elseif (strip_tags($descriptionSource) === $descriptionSource) {
        $renderHtml = nl2br(htmlspecialchars($descriptionSource, ENT_QUOTES, 'UTF-8'));
    } else {
        $renderHtml = $descriptionSource;
    }

    $noteDateRaw = trim((string) (isset($row->noteDate) ? $row->noteDate : ''));
    $noteTimestamp = $noteDateRaw !== '' ? strtotime($noteDateRaw) : false;
    $dateShort = $noteTimestamp ? date('M d, Y', $noteTimestamp) : 'No date';
    $dateFull = $noteTimestamp ? date('M d, Y \a\t h:i A', $noteTimestamp) : 'No date available';

    $tagsText = trim((string) (isset($row->tags) ? $row->tags : ''));
    $tags = array();
    if ($tagsText !== '') {
        foreach (explode(',', $tagsText) as $tag) {
            $tag = trim($tag);
            if ($tag === '') {
                continue;
            }

            $tags[] = $tag;
            $tagKey = strtolower($tag);
            if (!isset($uniqueTagsMap[$tagKey])) {
                $uniqueTagsMap[$tagKey] = $tag;
            }
        }
    }

    $isFavorite = !empty($row->is_favorite) ? 1 : 0;
    if ($isFavorite) {
        $favoritesCount++;
    }

    $wordCount = $plainText === '' ? 0 : count(preg_split('/\s+/', $plainText));

    $noteLinks = array();
    if ($descriptionSource !== '' && preg_match_all('/https?:\/\/[^\s<>"{}|\\\\^`\\[\\]]+/i', $descriptionSource, $matches)) {
        foreach ($matches[0] as $match) {
            if (!in_array($match, $noteLinks, true)) {
                $noteLinks[] = $match;
            }
        }
    }

    if (!empty($noteLinks)) {
        $linkedNotesCount++;
    }

    $notes[] = array(
        'id' => $noteId,
        'title' => $titleRaw,
        'displayTitle' => $displayTitle,
        'descriptionSource' => $descriptionSource,
        'descriptionHtml' => $renderHtml,
        'plainText' => $plainText,
        'dateShort' => $dateShort,
        'dateFull' => $dateFull,
        'tags' => array_values($tags),
        'tagsText' => $tagsText,
        'favorite' => $isFavorite,
        'wordCount' => $wordCount,
        'linkCount' => count($noteLinks),
        'links' => $noteLinks,
    );
}

$tagOptions = array_values($uniqueTagsMap);
natcasesort($tagOptions);
$tagOptions = array_values($tagOptions);

$totalNotes = count($notes);
$totalTagCount = count($tagOptions);
$initialNoteId = $totalNotes > 0 ? (int) $notes[0]['id'] : 0;

$notesJson = json_encode(
    $notes,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES
);
?>
<!DOCTYPE html>
<html lang="en">

<?php include('includes/head.php'); ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<style>
    .notes-modern-page,
    .notes-modern-page .content-page {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }

    .notes-modern-page {
        --notes-bg: #f4f8fc;
        --notes-surface: rgba(255, 255, 255, 0.9);
        --notes-surface-strong: #ffffff;
        --notes-border: rgba(148, 163, 184, 0.2);
        --notes-border-strong: rgba(148, 163, 184, 0.35);
        --notes-text: #102033;
        --notes-text-soft: #5b6b7f;
        --notes-text-muted: #91a0b3;
        --notes-primary: #1d4ed8;
        --notes-primary-deep: #0f3ea8;
        --notes-accent: #0f766e;
        --notes-warning: #d97706;
        --notes-danger: #dc2626;
        --notes-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
        --notes-shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.06);
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 28%),
            radial-gradient(circle at top right, rgba(15, 118, 110, 0.12), transparent 24%),
            linear-gradient(180deg, #f8fbff 0%, #f1f6fb 100%);
        min-height: 100vh;
    }

    .notes-modern-page .content {
        background: transparent;
    }

    .notes-modern-page .notes-workspace {
        padding: 24px 12px 40px;
    }

    .notes-modern-page .notes-hero {
        position: relative;
        overflow: hidden;
        padding: 28px;
        border-radius: 28px;
        background:
            linear-gradient(135deg, rgba(13, 26, 51, 0.95) 0%, rgba(16, 86, 169, 0.92) 58%, rgba(15, 118, 110, 0.88) 100%);
        color: #ffffff;
        box-shadow: var(--notes-shadow);
        margin-bottom: 22px;
    }

    .notes-modern-page .notes-hero::before,
    .notes-modern-page .notes-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
    }

    .notes-modern-page .notes-hero::before {
        width: 240px;
        height: 240px;
        right: -90px;
        top: -120px;
        background: rgba(255, 255, 255, 0.1);
    }

    .notes-modern-page .notes-hero::after {
        width: 180px;
        height: 180px;
        left: 52%;
        bottom: -110px;
        background: rgba(255, 255, 255, 0.08);
    }

    .notes-modern-page .notes-hero__top {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .notes-modern-page .notes-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.76rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.78);
        margin-bottom: 10px;
    }

    .notes-modern-page .notes-eyebrow::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #f8fafc;
        box-shadow: 0 0 0 6px rgba(248, 250, 252, 0.12);
    }

    .notes-modern-page .notes-hero h1 {
        margin: 0;
        font-size: 2.3rem;
        line-height: 1.05;
        font-weight: 800;
        letter-spacing: -0.04em;
        color: #ffffff !important;
        text-shadow: 0 2px 12px rgba(15, 23, 42, 0.28);
    }

    .notes-modern-page .notes-hero p {
        max-width: 620px;
        margin: 12px 0 0;
        color: rgba(255, 255, 255, 0.9) !important;
        text-shadow: 0 1px 8px rgba(15, 23, 42, 0.22);
        font-size: 0.96rem;
        line-height: 1.7;
    }

    .notes-modern-page .notes-hero__actions {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .notes-modern-page .notes-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1px solid transparent;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none !important;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease, background 0.18s ease;
        cursor: pointer;
    }

    .notes-modern-page .notes-btn:hover,
    .notes-modern-page .notes-btn:focus {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .notes-modern-page .notes-btn--primary {
        color: #0f172a;
        background: #ffffff;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.16);
    }

    .notes-modern-page .notes-btn--solid {
        color: #ffffff;
        background: linear-gradient(135deg, var(--notes-primary) 0%, var(--notes-accent) 100%);
        box-shadow: 0 14px 28px rgba(29, 78, 216, 0.2);
    }

    .notes-modern-page .notes-btn--ghost {
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.22);
        background: rgba(255, 255, 255, 0.08);
    }

    .notes-modern-page .notes-btn--ghost:hover,
    .notes-modern-page .notes-btn--ghost:focus {
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.4);
        background: rgba(255, 255, 255, 0.14);
    }

    .notes-modern-page .notes-stats {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .notes-modern-page .notes-stat {
        padding: 18px 18px 16px;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.14);
        backdrop-filter: blur(14px);
    }

    .notes-modern-page .notes-stat span {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.74);
    }

    .notes-modern-page .notes-stat strong {
        display: block;
        margin-top: 8px;
        font-size: 1.7rem;
        line-height: 1;
        font-weight: 800;
        letter-spacing: -0.04em;
    }

    .notes-modern-page .notes-shell {
        display: block;
        min-height: 680px;
        border-radius: 28px;
        overflow: hidden;
        background: var(--notes-surface);
        border: 1px solid var(--notes-border);
        box-shadow: var(--notes-shadow);
        backdrop-filter: blur(18px);
    }

    .notes-modern-page .notes-rail {
        display: flex;
        flex-direction: column;
        min-width: 0;
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.94) 0%, rgba(243, 247, 252, 0.98) 100%);
    }

    .notes-modern-page .notes-rail__header {
        padding: 22px 22px 18px;
        border-bottom: 1px solid var(--notes-border);
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(14px);
    }

    .notes-modern-page .notes-rail__header h2 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--notes-text);
    }

    .notes-modern-page .notes-rail__header p {
        margin: 6px 0 0;
        font-size: 0.84rem;
        color: var(--notes-text-soft);
    }

    .notes-modern-page .notes-filter-panel {
        padding: 18px 22px 16px;
        border-bottom: 1px solid var(--notes-border);
    }

    .notes-modern-page .notes-filter-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 12px;
    }

    .notes-modern-page .notes-field {
        position: relative;
    }

    .notes-modern-page .notes-field i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--notes-text-muted);
        font-size: 1rem;
    }

    .notes-modern-page .notes-input,
    .notes-modern-page .notes-select {
        width: 100%;
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid var(--notes-border);
        background: #ffffff;
        color: var(--notes-text);
        font-size: 0.89rem;
        padding: 0 14px;
        box-shadow: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease;
    }

    .notes-modern-page .notes-input {
        padding-left: 42px;
    }

    .notes-modern-page .notes-input:focus,
    .notes-modern-page .notes-select:focus {
        outline: none;
        border-color: rgba(29, 78, 216, 0.4);
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.1);
    }

    .notes-modern-page .notes-filter-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 12px;
    }

    .notes-modern-page .notes-pill-button {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 40px;
        padding: 0 14px;
        border-radius: 999px;
        border: 1px solid var(--notes-border);
        background: #ffffff;
        color: var(--notes-text-soft);
        font-size: 0.84rem;
        font-weight: 700;
        transition: all 0.18s ease;
    }

    .notes-modern-page .notes-pill-button.is-active {
        background: rgba(29, 78, 216, 0.08);
        border-color: rgba(29, 78, 216, 0.24);
        color: var(--notes-primary);
    }

    .notes-modern-page .notes-visible-count {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--notes-text-muted);
    }

    .notes-modern-page .notes-list {
        flex: 1 1 auto;
        min-height: 0;
        max-height: 760px;
        overflow-y: auto;
        padding: 14px;
    }

    .notes-modern-page .notes-list-item {
        width: 100%;
        border: 1px solid transparent;
        border-radius: 18px;
        background: transparent;
        padding: 16px 18px;
        margin-bottom: 10px;
        text-align: left;
        cursor: pointer;
        transition: all 0.18s ease;
    }

    .notes-modern-page .notes-list-item:last-child {
        margin-bottom: 0;
    }

    .notes-modern-page .notes-list-item:hover,
    .notes-modern-page .notes-list-item:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.88);
        border-color: rgba(148, 163, 184, 0.22);
        box-shadow: var(--notes-shadow-soft);
        transform: translateY(-1px);
    }

    .notes-modern-page .notes-list-item.is-active {
        color: #ffffff;
        background: linear-gradient(135deg, var(--notes-primary) 0%, var(--notes-accent) 100%);
        border-color: transparent;
        box-shadow: 0 18px 34px rgba(29, 78, 216, 0.28);
    }

    .notes-modern-page .notes-list-item span {
        display: block;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .notes-modern-page .notes-list-empty,
    .notes-modern-page .notes-detail-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-align: center;
        color: var(--notes-text-muted);
    }

    .notes-modern-page .notes-list-empty {
        min-height: 280px;
        padding: 26px 18px;
    }

    .notes-modern-page .notes-list-empty i,
    .notes-modern-page .notes-detail-empty i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 64px;
        height: 64px;
        border-radius: 20px;
        background: rgba(29, 78, 216, 0.08);
        color: var(--notes-primary);
        font-size: 1.7rem;
    }

    .notes-modern-page .notes-list-empty strong,
    .notes-modern-page .notes-detail-empty strong {
        font-size: 1rem;
        color: var(--notes-text);
    }

    .notes-modern-page .notes-list-empty p,
    .notes-modern-page .notes-detail-empty p {
        max-width: 280px;
        margin: 0;
        font-size: 0.88rem;
        line-height: 1.6;
    }

    .notes-modern-page .notes-detail {
        display: flex;
        flex-direction: column;
        min-width: 0;
        background: rgba(255, 255, 255, 0.94);
    }

    .notes-modern-page .notes-detail__canvas {
        height: 100%;
        min-height: 680px;
        overflow-y: auto;
        padding: 28px;
    }

    .notes-modern-page .notes-detail__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--notes-border);
    }

    .notes-modern-page .notes-detail__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--notes-text-muted);
        margin-bottom: 10px;
    }

    .notes-modern-page .notes-detail__eyebrow::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--notes-primary);
    }

    .notes-modern-page .notes-detail__title {
        margin: 0;
        font-size: 2rem;
        line-height: 1.08;
        font-weight: 800;
        letter-spacing: -0.04em;
        color: var(--notes-text);
        word-break: break-word;
    }

    .notes-modern-page .notes-detail__meta {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .notes-modern-page .notes-detail__meta-item {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: #f8fafc;
        border: 1px solid var(--notes-border);
        color: var(--notes-text-soft);
        font-size: 0.82rem;
        font-weight: 700;
    }

    .notes-modern-page .notes-detail__actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .notes-modern-page .notes-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 15px;
        border-radius: 14px;
        border: 1px solid var(--notes-border);
        background: #ffffff;
        color: var(--notes-text-soft);
        font-size: 0.86rem;
        font-weight: 700;
        transition: all 0.18s ease;
    }

    .notes-modern-page .notes-action:hover,
    .notes-modern-page .notes-action:focus {
        outline: none;
        transform: translateY(-1px);
        box-shadow: var(--notes-shadow-soft);
    }

    .notes-modern-page .notes-action--favorite.is-active {
        color: var(--notes-warning);
        border-color: rgba(217, 119, 6, 0.22);
        background: rgba(217, 119, 6, 0.08);
    }

    .notes-modern-page .notes-action--danger {
        color: var(--notes-danger);
    }

    .notes-modern-page .notes-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 24px;
    }

    .notes-modern-page .notes-tag {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        min-height: 34px;
        padding: 0 12px;
        border-radius: 999px;
        background: rgba(29, 78, 216, 0.08);
        color: var(--notes-primary);
        border: 1px solid rgba(29, 78, 216, 0.12);
        font-size: 0.79rem;
        font-weight: 700;
    }

    .notes-modern-page .notes-detail__body {
        margin-top: 24px;
        padding: 22px 24px;
        border-radius: 24px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid var(--notes-border);
    }

    .notes-modern-page .notes-detail__section-title {
        margin: 0 0 16px;
        font-size: 0.92rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--notes-text-muted);
    }

    .notes-modern-page .notes-detail__content {
        color: var(--notes-text);
        font-size: 0.96rem;
        line-height: 1.85;
        word-break: break-word;
    }

    .notes-modern-page .notes-detail__content p,
    .notes-modern-page .notes-detail__content ul,
    .notes-modern-page .notes-detail__content ol,
    .notes-modern-page .notes-detail__content blockquote,
    .notes-modern-page .notes-detail__content pre,
    .notes-modern-page .notes-detail__content h1,
    .notes-modern-page .notes-detail__content h2,
    .notes-modern-page .notes-detail__content h3,
    .notes-modern-page .notes-detail__content h4,
    .notes-modern-page .notes-detail__content h5,
    .notes-modern-page .notes-detail__content h6 {
        margin-top: 0;
        margin-bottom: 1rem;
    }

    .notes-modern-page .notes-detail__content a {
        color: var(--notes-primary);
        font-weight: 700;
        text-decoration: none;
    }

    .notes-modern-page .notes-detail__content a:hover,
    .notes-modern-page .notes-detail__content a:focus {
        text-decoration: underline;
    }

    .notes-modern-page .notes-detail__content blockquote {
        padding: 14px 18px;
        border-left: 4px solid rgba(29, 78, 216, 0.28);
        background: rgba(29, 78, 216, 0.06);
        border-radius: 0 16px 16px 0;
        color: var(--notes-text-soft);
    }

    .notes-modern-page .notes-detail__content pre {
        overflow-x: auto;
        padding: 16px;
        border-radius: 16px;
        background: #0f172a;
        color: #e2e8f0;
    }

    .notes-modern-page .notes-detail__content img {
        max-width: 100%;
        height: auto;
        border-radius: 18px;
        box-shadow: var(--notes-shadow-soft);
    }

    .notes-modern-page .notes-detail__content .ql-align-center {
        text-align: center;
    }

    .notes-modern-page .notes-detail__content .ql-align-right {
        text-align: right;
    }

    .notes-modern-page .notes-detail__content .ql-align-justify {
        text-align: justify;
    }

    .notes-modern-page .notes-links {
        margin-top: 24px;
        padding: 22px 24px;
        border-radius: 24px;
        border: 1px solid var(--notes-border);
        background: #ffffff;
    }

    .notes-modern-page .notes-links__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        margin-top: 16px;
    }

    .notes-modern-page .notes-link-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        padding: 15px 16px;
        border-radius: 18px;
        border: 1px solid var(--notes-border);
        background: #f8fbff;
        color: var(--notes-text);
        text-decoration: none !important;
        transition: all 0.18s ease;
    }

    .notes-modern-page .notes-link-card:hover,
    .notes-modern-page .notes-link-card:focus {
        transform: translateY(-1px);
        box-shadow: var(--notes-shadow-soft);
        border-color: rgba(29, 78, 216, 0.22);
    }

    .notes-modern-page .notes-link-card i {
        flex: 0 0 auto;
        width: 40px;
        height: 40px;
        border-radius: 14px;
        background: rgba(29, 78, 216, 0.08);
        color: var(--notes-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }

    .notes-modern-page .notes-link-card span {
        min-width: 0;
        display: block;
        font-size: 0.88rem;
        font-weight: 700;
    }

    .notes-modern-page .notes-modal .modal-content {
        border: 0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 30px 70px rgba(15, 23, 42, 0.18);
    }

    .notes-modern-page .notes-modal--viewer .modal-dialog {
        max-width: 1080px;
    }

    .notes-modern-page .notes-modal--viewer .modal-body {
        padding: 0;
        background: #f7fbff;
    }

    .notes-modern-page .notes-modal--viewer .notes-detail__canvas {
        min-height: 0;
        max-height: calc(100vh - 160px);
        padding: 28px 24px 30px;
    }

    .notes-modern-page .notes-modal .modal-header {
        padding: 22px 24px;
        border-bottom: 0;
        color: #ffffff;
    }

    .notes-modern-page .notes-modal .modal-header--primary {
        background: linear-gradient(135deg, #0f3ea8 0%, #1d4ed8 100%);
    }

    .notes-modern-page .notes-modal .modal-header--accent {
        background: linear-gradient(135deg, #0f766e 0%, #0f8a7b 100%);
    }

    .notes-modern-page .notes-modal .modal-title {
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #ffffff !important;
        text-shadow: 0 1px 8px rgba(15, 23, 42, 0.24);
    }

    .notes-modern-page .notes-modal .close {
        color: #ffffff;
        text-shadow: none;
        opacity: 0.88;
    }

    .notes-modern-page .notes-modal .modal-body {
        padding: 24px;
    }

    .notes-modern-page .notes-modal .modal-footer {
        padding: 0 24px 24px;
        border-top: 0;
        gap: 10px;
    }

    .notes-modern-page .notes-modal .notes-btn--neutral {
        color: var(--notes-text-soft);
        background: #ffffff;
        border-color: var(--notes-border);
        box-shadow: none;
    }

    .notes-modern-page .notes-modal .notes-btn--neutral:hover,
    .notes-modern-page .notes-modal .notes-btn--neutral:focus {
        color: var(--notes-text);
        border-color: var(--notes-border-strong);
        background: #ffffff;
    }

    .notes-modern-page .notes-modal .form-group label {
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--notes-text-soft);
        margin-bottom: 8px;
    }

    .notes-modern-page .notes-modal .form-control {
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid var(--notes-border);
        box-shadow: none;
        color: var(--notes-text);
    }

    .notes-modern-page .notes-modal .form-control:focus {
        border-color: rgba(29, 78, 216, 0.4);
        box-shadow: 0 0 0 4px rgba(29, 78, 216, 0.1);
    }

    .notes-modern-page .notes-editor-shell {
        border-radius: 18px;
        border: 1px solid var(--notes-border);
        overflow: hidden;
        background: #ffffff;
    }

    .notes-modern-page .ql-toolbar.ql-snow {
        border: 0;
        border-bottom: 1px solid var(--notes-border);
    }

    .notes-modern-page .ql-container.ql-snow {
        border: 0;
        font-family: 'Plus Jakarta Sans', sans-serif;
        min-height: 260px;
    }

    .notes-modern-page .notes-voice-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .notes-modern-page .notes-voice-status {
        font-size: 0.8rem;
        color: var(--notes-text-muted);
    }

    @media (max-width: 1199px) {
        .notes-modern-page .notes-detail__title {
            font-size: 1.8rem;
        }
    }

    @media (max-width: 991px) {
        .notes-modern-page .notes-workspace {
            padding-left: 8px;
            padding-right: 8px;
        }

        .notes-modern-page .notes-hero {
            padding: 24px 20px;
        }

        .notes-modern-page .notes-stats {
            grid-template-columns: 1fr;
        }

        .notes-modern-page .notes-detail__canvas {
            min-height: 520px;
            padding: 22px 20px 26px;
        }
    }

    @media (max-width: 575px) {
        .notes-modern-page .notes-hero h1 {
            font-size: 1.9rem;
        }

        .notes-modern-page .notes-detail__title {
            font-size: 1.55rem;
        }

        .notes-modern-page .notes-detail__header,
        .notes-modern-page .notes-detail__actions,
        .notes-modern-page .notes-filter-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .notes-modern-page .notes-action,
        .notes-modern-page .notes-pill-button,
        .notes-modern-page .notes-btn {
            width: 100%;
        }

        .notes-modern-page .notes-detail__body,
        .notes-modern-page .notes-links,
        .notes-modern-page .notes-rail__header,
        .notes-modern-page .notes-filter-panel {
            padding-left: 18px;
            padding-right: 18px;
        }

        .notes-modern-page .notes-list {
            padding: 12px;
        }
    }
</style>

<body class="notes-modern-page">

<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid notes-workspace">
                <section class="notes-hero">
                    <div class="notes-hero__top">
                        <div>
                            <span class="notes-eyebrow">Notes workspace</span>
                            <h1>Keep every note sharp and easy to revisit.</h1>
                        </div>

                        <div class="notes-hero__actions">
                            <button type="button" class="notes-btn notes-btn--primary" data-toggle="modal" data-target="#newnotes">
                                <i class="mdi mdi-plus-circle-outline"></i>
                                New Note
                            </button>
                            <a class="notes-btn notes-btn--ghost" href="<?= base_url('Page/noteList'); ?>">
                                <i class="mdi mdi-refresh"></i>
                                Refresh
                            </a>
                        </div>
                    </div>

                    <div class="notes-stats">
                        <div class="notes-stat">
                            <span>Total notes</span>
                            <strong><?= number_format($totalNotes); ?></strong>
                        </div>
                        <div class="notes-stat">
                            <span>Favorites</span>
                            <strong><?= number_format($favoritesCount); ?></strong>
                        </div>
                        <div class="notes-stat">
                            <span>Tagged collections</span>
                            <strong><?= number_format($totalTagCount); ?></strong>
                        </div>
                    </div>
                </section>

                <section class="notes-shell">
                    <aside class="notes-rail">
                        <div class="notes-rail__header">
                            <h2>Note index</h2>
                        </div>

                        <div class="notes-filter-panel">
                            <div class="notes-filter-grid">
                                <div class="notes-field">
                                    <i class="mdi mdi-magnify"></i>
                                    <input type="text" id="noteSearch" class="notes-input" placeholder="Search by title, tag, or content">
                                </div>

                                <select class="notes-select" id="tagFilter">
                                    <option value="">All tags</option>
                                    <?php foreach ($tagOptions as $tagOption): ?>
                                        <option value="<?= htmlspecialchars($tagOption, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($tagOption, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="notes-filter-actions">
                                <button type="button" class="notes-pill-button" id="favoritesToggle">
                                    <i class="mdi mdi-star-outline"></i>
                                    Favorites only
                                </button>
                                <span class="notes-visible-count" id="notesVisibleCount"><?= number_format($totalNotes); ?> shown</span>
                            </div>
                        </div>

                        <div class="notes-list" id="notesList">
                            <noscript>
                                <div class="notes-list-empty">
                                    <i class="mdi mdi-alert-circle-outline"></i>
                                    <strong>JavaScript is required</strong>
                                    <p>Turn on JavaScript to use the interactive note list and detail modal.</p>
                                </div>
                            </noscript>
                        </div>
                    </aside>
                </section>
            </div>
        </div>
    </div>
</div>

<?php include('includes/themecustomizer.php'); ?>

<form id="deleteNoteForm" method="post" action="<?= base_url('Page/noteList'); ?>" class="d-none">
    <input type="hidden" name="noteID" id="deleteNoteID" value="">
    <input type="hidden" name="delete_note" value="1">
</form>

<div class="modal fade notes-modal notes-modal--viewer" id="viewNoteModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header--primary">
                <h5 class="modal-title mb-0">Note Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="notes-detail__canvas" id="viewNoteModalBody">
                    <div class="notes-detail-empty">
                        <i class="mdi mdi-notebook-outline"></i>
                        <strong>Select a note</strong>
                        <p>Choose a title from the list to open the note details here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade notes-modal" id="newnotes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header--primary">
                <h5 class="modal-title mb-0">Create Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="needs-validation" method="post" action="<?= base_url('Page/noteList'); ?>" id="newNoteForm">
                <input type="hidden" name="addnote" value="1">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="noteTitle">Title</label>
                        <input type="text" class="form-control" name="noteTitle" id="noteTitle" required>
                    </div>

                    <div class="form-group">
                        <label for="noteTags">Tags</label>
                        <input type="text" class="form-control" name="noteTags" id="noteTags">
                    </div>

                    <div class="form-group mb-0">
                        <label for="noteDescription">Notes</label>
                        <div class="notes-voice-row">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="startVoiceInput('noteDescriptionEditor')" id="voiceBtnNote">
                                <i class="mdi mdi-microphone"></i>
                                Voice Input
                            </button>
                            <span id="voiceStatusNote" class="notes-voice-status"></span>
                        </div>

                        <div class="notes-editor-shell">
                            <div id="noteDescriptionEditor"></div>
                        </div>
                        <input type="hidden" name="noteDescription" id="noteDescription">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" id="saveNoteBtn" class="notes-btn notes-btn--solid">Save Note</button>
                    <button type="button" class="notes-btn notes-btn--neutral" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade notes-modal" id="editnotes" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header modal-header--accent">
                <h5 class="modal-title mb-0">Edit Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form class="needs-validation" method="post" action="<?= base_url('Page/updateNote'); ?>" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="noteID" id="editNoteID">

                    <div class="form-group">
                        <label for="editNoteTitle">Title</label>
                        <input type="text" class="form-control" name="noteTitle" id="editNoteTitle" required>
                    </div>

                    <div class="form-group">
                        <label for="editNoteTags">Tags</label>
                        <input type="text" class="form-control" name="noteTags" id="editNoteTags">
                    </div>

                    <div class="form-group mb-0">
                        <label for="editNoteDescription">Notes</label>
                        <div class="notes-voice-row">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="startVoiceInput('editNoteDescriptionEditor')" id="voiceBtnEdit">
                                <i class="mdi mdi-microphone"></i>
                                Voice Input
                            </button>
                            <span id="voiceStatusEdit" class="notes-voice-status"></span>
                        </div>

                        <div class="notes-editor-shell">
                            <div id="editNoteDescriptionEditor"></div>
                        </div>
                        <input type="hidden" name="noteDescription" id="editNoteDescription" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="notes-btn notes-btn--solid">Update Note</button>
                    <button type="button" class="notes-btn notes-btn--neutral" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script src="<?= base_url(); ?>assets/libs/jquery-ui/jquery-ui.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>

<script>
    var noteDescriptionEditor;
    var editNoteDescriptionEditor;
    var notesData = <?= $notesJson ? $notesJson : '[]'; ?>;
    var selectedNoteId = <?= (int) $initialNoteId; ?>;
    var favoritesOnly = false;

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getNoteById(noteId) {
        noteId = parseInt(noteId, 10);
        for (var index = 0; index < notesData.length; index++) {
            if (parseInt(notesData[index].id, 10) === noteId) {
                return notesData[index];
            }
        }

        return null;
    }

    function getFilteredNotes() {
        var searchValue = ($('#noteSearch').val() || '').toLowerCase().trim();
        var selectedTag = $('#tagFilter').val() || '';

        return notesData.filter(function(note) {
            var title = (note.displayTitle || '').toLowerCase();
            var plainText = (note.plainText || '').toLowerCase();
            var tagsText = (note.tagsText || '').toLowerCase();
            var matchesSearch = searchValue === '' ||
                title.indexOf(searchValue) !== -1 ||
                plainText.indexOf(searchValue) !== -1 ||
                tagsText.indexOf(searchValue) !== -1;

            var matchesTag = selectedTag === '' || (note.tags || []).some(function(tag) {
                return String(tag || '').toLowerCase() === selectedTag.toLowerCase();
            });
            var matchesFavorite = !favoritesOnly || parseInt(note.favorite, 10) === 1;

            return matchesSearch && matchesTag && matchesFavorite;
        });
    }

    function renderNotesList() {
        var filteredNotes = getFilteredNotes();
        var listHtml = '';

        if (!filteredNotes.length) {
            selectedNoteId = 0;
            listHtml =
                '<div class="notes-list-empty">' +
                    '<i class="mdi mdi-file-search-outline"></i>' +
                    '<strong>No notes matched</strong>' +
                    '<p>Try a different keyword or tag filter to bring notes back into view.</p>' +
                '</div>';
        } else {
            var hasSelectedNote = false;

            filteredNotes.forEach(function(note) {
                var noteId = parseInt(note.id, 10);
                if (noteId === parseInt(selectedNoteId, 10)) {
                    hasSelectedNote = true;
                }

                listHtml +=
                    '<button type="button" class="notes-list-item' + (noteId === parseInt(selectedNoteId, 10) ? ' is-active' : '') + '" onclick="selectNote(' + noteId + ')">' +
                        '<span>' + escapeHtml(note.displayTitle || 'Untitled Note') + '</span>' +
                    '</button>';
            });

            if (!hasSelectedNote) {
                selectedNoteId = parseInt(filteredNotes[0].id, 10);
                return renderNotesList();
            }
        }

        $('#notesList').html(listHtml);
        $('#notesVisibleCount').text(filteredNotes.length + ' shown');
    }

    function buildNoteDetailHtml(noteId) {
        var note = getNoteById(noteId);

        if (!note) {
            return (
                '<div class="notes-detail-empty">' +
                    '<i class="mdi mdi-notebook-outline"></i>' +
                    '<strong>No note selected</strong>' +
                    '<p>Pick a note from the list, or create a new one to start capturing details.</p>' +
                '</div>'
            );
        }

        selectedNoteId = parseInt(note.id, 10);

        var metaHtml =
            '<div class="notes-detail__meta">' +
                '<span class="notes-detail__meta-item"><i class="mdi mdi-calendar-blank-outline"></i>' + escapeHtml(note.dateFull || 'No date available') + '</span>' +
                '<span class="notes-detail__meta-item"><i class="mdi mdi-text-box-outline"></i>' + escapeHtml(String(note.wordCount || 0)) + ' words</span>';

        if (parseInt(note.linkCount, 10) > 0) {
            metaHtml += '<span class="notes-detail__meta-item"><i class="mdi mdi-link-variant"></i>' + escapeHtml(String(note.linkCount)) + ' links</span>';
        }

        metaHtml += '</div>';

        var tagsHtml = '';
        if ((note.tags || []).length) {
            tagsHtml = '<div class="notes-tags">';
            note.tags.forEach(function(tag) {
                tagsHtml += '<span class="notes-tag"><i class="mdi mdi-pound"></i>' + escapeHtml(tag) + '</span>';
            });
            tagsHtml += '</div>';
        }

        var linksHtml = '';
        if ((note.links || []).length) {
            linksHtml =
                '<div class="notes-links">' +
                    '<h3 class="notes-detail__section-title">Linked resources</h3>' +
                    '<div class="notes-links__grid">';

            note.links.forEach(function(link) {
                linksHtml +=
                    '<a class="notes-link-card" href="' + escapeHtml(link) + '" target="_blank" rel="noopener noreferrer">' +
                        '<i class="mdi mdi-open-in-new"></i>' +
                        '<span>Open Link</span>' +
                    '</a>';
            });

            linksHtml += '</div></div>';
        }

        var descriptionHtml = note.descriptionHtml ? note.descriptionHtml : '<p class="text-muted mb-0">No content added yet.</p>';

        var detailHtml =
            '<div class="notes-detail__header">' +
                '<div>' +
                    '<div class="notes-detail__eyebrow">Selected note</div>' +
                    '<h2 class="notes-detail__title">' + escapeHtml(note.displayTitle || 'Untitled Note') + '</h2>' +
                    metaHtml +
                '</div>' +
                '<div class="notes-detail__actions">' +
                    '<button type="button" class="notes-action notes-action--favorite' + (parseInt(note.favorite, 10) === 1 ? ' is-active' : '') + '" onclick="toggleFavorite(' + parseInt(note.id, 10) + ')">' +
                        '<i class="mdi ' + (parseInt(note.favorite, 10) === 1 ? 'mdi-star' : 'mdi-star-outline') + '"></i>' +
                        (parseInt(note.favorite, 10) === 1 ? 'Favorited' : 'Favorite') +
                    '</button>' +
                    '<button type="button" class="notes-action" onclick="openEditModal(' + parseInt(note.id, 10) + ')">' +
                        '<i class="mdi mdi-pencil-outline"></i>Edit' +
                    '</button>' +
                    '<button type="button" class="notes-action notes-action--danger" onclick="deleteNote(' + parseInt(note.id, 10) + ')">' +
                        '<i class="mdi mdi-trash-can-outline"></i>Delete' +
                    '</button>' +
                '</div>' +
            '</div>' +
            tagsHtml +
            '<div class="notes-detail__body">' +
                '<h3 class="notes-detail__section-title">Note content</h3>' +
                '<div class="notes-detail__content">' + descriptionHtml + '</div>' +
            '</div>' +
            linksHtml;

        return detailHtml;
    }

    function renderViewModal(noteId) {
        $('#viewNoteModalBody').html(buildNoteDetailHtml(noteId));
    }

    function openViewModal(noteId) {
        selectedNoteId = parseInt(noteId, 10);
        renderViewModal(noteId);
        $('#viewNoteModal').modal('show');
    }

    function selectNote(noteId) {
        selectedNoteId = parseInt(noteId, 10);
        renderNotesList();
        openViewModal(noteId);
    }

    function syncFavoriteCount() {
        var favoritesCount = notesData.filter(function(note) {
            return parseInt(note.favorite, 10) === 1;
        }).length;

        $('.notes-stat strong').eq(1).text(favoritesCount.toLocaleString());
    }

    function toggleFavorite(noteId) {
        var note = getNoteById(noteId);
        if (!note) {
            return;
        }

        var newStatus = parseInt(note.favorite, 10) === 1 ? 0 : 1;

        $.ajax({
            url: '<?= base_url('Page/toggleFavorite'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                note_id: noteId,
                is_favorite: newStatus
            },
            success: function(response) {
                if (!response || !response.success) {
                    alert('Failed to update favorite status.');
                    return;
                }

                note.favorite = newStatus;
                syncFavoriteCount();
                renderNotesList();

                if ($('#viewNoteModal').hasClass('show')) {
                    renderViewModal(noteId);
                }
            },
            error: function() {
                alert('Failed to update favorite status. Please try again.');
            }
        });
    }

    function openEditModal(noteId) {
        var note = getNoteById(noteId || selectedNoteId);
        if (!note) {
            return;
        }

        $('#viewNoteModal').modal('hide');
        $('#editNoteID').val(note.id);
        $('#editNoteTitle').val(note.title || '');
        $('#editNoteTags').val(note.tagsText || '');
        editNoteDescriptionEditor.root.innerHTML = note.descriptionSource || '';
        $('#editnotes').modal('show');
    }

    function deleteNote(noteId) {
        var note = getNoteById(noteId || selectedNoteId);
        if (!note) {
            return;
        }

        if (!window.confirm('Delete "' + (note.displayTitle || 'this note') + '"?')) {
            return;
        }

        $('#viewNoteModal').modal('hide');
        $('#deleteNoteID').val(note.id);
        $('#deleteNoteForm').trigger('submit');
    }

    function startVoiceInput(editorId) {
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            alert('Voice input is not supported in your browser. Please use Chrome or Edge.');
            return;
        }

        var SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        var recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = false;
        recognition.lang = 'en-US';

        var editor;
        var statusEl;
        var btnEl;

        if (editorId === 'noteDescriptionEditor') {
            editor = noteDescriptionEditor;
            statusEl = document.getElementById('voiceStatusNote');
            btnEl = document.getElementById('voiceBtnNote');
        } else {
            editor = editNoteDescriptionEditor;
            statusEl = document.getElementById('voiceStatusEdit');
            btnEl = document.getElementById('voiceBtnEdit');
        }

        recognition.onstart = function() {
            statusEl.textContent = 'Listening...';
            btnEl.classList.remove('btn-outline-info');
            btnEl.classList.add('btn-danger');
            btnEl.innerHTML = '<i class="mdi mdi-stop"></i> Stop';
        };

        recognition.onresult = function(event) {
            for (var index = event.resultIndex; index < event.results.length; index++) {
                if (!event.results[index].isFinal) {
                    continue;
                }

                var transcript = event.results[index][0].transcript;
                editor.focus();
                var range = editor.getSelection(true);
                var insertIndex = range ? range.index : editor.getLength();
                editor.insertText(insertIndex, transcript + ' ');
                editor.setSelection(insertIndex + transcript.length + 1, 0);
            }
        };

        recognition.onerror = function(event) {
            statusEl.textContent = 'Error: ' + event.error;
            resetVoiceButton();
        };

        recognition.onend = function() {
            statusEl.textContent = '';
            resetVoiceButton();
        };

        function resetVoiceButton() {
            btnEl.classList.remove('btn-danger');
            btnEl.classList.add('btn-outline-info');
            btnEl.innerHTML = '<i class="mdi mdi-microphone"></i> Voice Input';
        }

        recognition.start();
    }

    document.addEventListener('DOMContentLoaded', function() {
        noteDescriptionEditor = new Quill('#noteDescriptionEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ indent: '-1' }, { indent: '+1' }],
                    [{ color: [] }, { background: [] }],
                    [{ align: [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        editNoteDescriptionEditor = new Quill('#editNoteDescriptionEditor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    [{ indent: '-1' }, { indent: '+1' }],
                    [{ color: [] }, { background: [] }],
                    [{ align: [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        $('#newNoteForm').on('submit', function() {
            if (noteDescriptionEditor) {
                $('#noteDescription').val(noteDescriptionEditor.root.innerHTML);
            }
        });

        $('#editnotes form').on('submit', function() {
            $('#editNoteDescription').val(editNoteDescriptionEditor.root.innerHTML);
        });

        $('#newnotes').on('hidden.bs.modal', function() {
            this.querySelector('form').reset();
            noteDescriptionEditor.root.innerHTML = '';
            $('#voiceStatusNote').text('');
        });

        $('#noteSearch').on('input', renderNotesList);
        $('#tagFilter').on('change', renderNotesList);

        $('#favoritesToggle').on('click', function() {
            favoritesOnly = !favoritesOnly;
            $(this)
                .toggleClass('is-active', favoritesOnly)
                .html(
                    '<i class="mdi ' + (favoritesOnly ? 'mdi-star' : 'mdi-star-outline') + '"></i>' +
                    (favoritesOnly ? 'Showing favorites' : 'Favorites only')
                );
            renderNotesList();
        });

        renderNotesList();
    });
</script>

</body>

</html>
