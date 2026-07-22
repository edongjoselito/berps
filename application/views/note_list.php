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
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">


<body class="notes-modern-page">

<div id="wrapper">
    <?php include('includes/top-nav-bar.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid notes-workspace berps-page">
                <section class="notes-hero">
                    <div class="notes-hero__top">
                        <div>
                            <span class="notes-eyebrow"><i class="mdi mdi-notebook-outline"></i> Notes workspace</span>
                            <h1>Keep every note sharp and easy to revisit. <span class="note-write">📝</span></h1>
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
