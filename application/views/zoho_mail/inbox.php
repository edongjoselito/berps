<!DOCTYPE html>
<html lang="en">
<?php include(APPPATH.'views/includes/head.php'); ?>
<body>
<div id="wrapper">
    <?php include(APPPATH.'views/includes/top-nav-bar.php'); ?>
    <?php include(APPPATH.'views/includes/sidebar.php'); ?>

    <div class="content-page">
        <div class="content">
            <div class="container-fluid">

                <div class="d-flex align-items-center justify-content-between mt-3 mb-3">
                    <h4 class="m-0"><i class="mdi mdi-inbox-arrow-down mr-1"></i> Zoho Mail
                        <small class="text-muted ml-2"><?= htmlspecialchars($account->primary_email ?? ''); ?></small>
                    </h4>
                    <div>
                        <a href="<?= base_url('ZohoMail/compose'); ?>" class="btn btn-primary btn-sm"><i class="mdi mdi-pencil-plus"></i> Compose</a>
                        <a href="<?= base_url('ZohoMail/settings'); ?>" class="btn btn-outline-secondary btn-sm"><i class="mdi mdi-cog"></i> Settings</a>
                    </div>
                </div>

                <?php if ($msg = $this->session->flashdata('success')): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($msg); ?></div>
                <?php endif; ?>
                <?php if ($msg = $this->session->flashdata('danger')): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($msg); ?></div>
                <?php endif; ?>
                <?php if (!empty($apiError)): ?>
                    <div class="alert alert-warning"><strong>API:</strong> <?= htmlspecialchars($apiError); ?></div>
                <?php endif; ?>

                <div class="row">
                    <!-- Folders -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body p-2">
                                <h6 class="text-uppercase text-muted small mt-1 mb-2 px-1">Folders</h6>
                                <ul class="list-group list-group-flush">
                                    <a href="<?= base_url('ZohoMail/inbox'); ?>" class="list-group-item list-group-item-action <?= $currentFolder===''?'active':''; ?>">
                                        <i class="mdi mdi-inbox"></i> Inbox
                                    </a>
                                    <?php foreach ($folders as $f): ?>
                                        <?php $fid = $f['folderId'] ?? ''; $fname = $f['folderName'] ?? '(unnamed)'; ?>
                                        <a href="<?= base_url('ZohoMail/inbox?folderId=' . rawurlencode($fid)); ?>"
                                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?= $currentFolder===$fid?'active':''; ?>">
                                            <span><i class="mdi mdi-folder-outline"></i> <?= htmlspecialchars($fname); ?></span>
                                            <?php if (!empty($f['unreadCount'])): ?>
                                                <span class="badge badge-primary badge-pill"><?= (int) $f['unreadCount']; ?></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </ul>

                                <hr>
                                <form method="post" action="<?= base_url('ZohoMail/folderAction'); ?>" class="px-2">
                                    <input type="hidden" name="mode" value="create">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="folderName" class="form-control" placeholder="New folder">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-outline-primary"><i class="mdi mdi-plus"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-body">
                                <form method="get" action="<?= base_url('ZohoMail/inbox'); ?>" class="form-inline mb-3">
                                    <?php if ($currentFolder !== ''): ?>
                                        <input type="hidden" name="folderId" value="<?= htmlspecialchars($currentFolder); ?>">
                                    <?php endif; ?>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search subject:hello, from:user@x.com, ..." value="<?= htmlspecialchars($search); ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary"><i class="mdi mdi-magnify"></i></button>
                                            <?php if ($search !== ''): ?>
                                                <a href="<?= base_url('ZohoMail/inbox'); ?>" class="btn btn-outline-secondary"><i class="mdi mdi-close"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </form>

                                <form method="post" action="<?= base_url('ZohoMail/messageAction'); ?>">
                                    <div class="form-row mb-2">
                                        <div class="col-auto">
                                            <select name="mode" class="form-control form-control-sm">
                                                <option value="markAsRead">Mark as read</option>
                                                <option value="markAsUnread">Mark as unread</option>
                                                <option value="flag">Flag</option>
                                                <option value="archive">Archive</option>
                                                <option value="trash">Move to Trash</option>
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm align-middle">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px"><input type="checkbox" id="selectAll"></th>
                                                    <th>From</th>
                                                    <th>Subject</th>
                                                    <th style="width:160px">Received</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (empty($messages)): ?>
                                                    <tr><td colspan="4" class="text-center text-muted py-4">No messages.</td></tr>
                                                <?php else: foreach ($messages as $m):
                                                    $mid = $m['messageId'] ?? '';
                                                    $fid = $m['folderId']  ?? $currentFolder;
                                                    $unread = isset($m['status']) ? ((string) $m['status'] === '0') : !empty($m['unread']);
                                                    $rowStyle = $unread ? 'font-weight:600;' : '';
                                                    $tsRaw = $m['receivedTime'] ?? ($m['sentDateInGMT'] ?? null);
                                                    $tsLabel = $tsRaw ? date('M j, Y g:ia', is_numeric($tsRaw) ? ((int)$tsRaw / 1000) : strtotime($tsRaw)) : '';
                                                ?>
                                                    <tr style="<?= $rowStyle ?>">
                                                        <td><input type="checkbox" name="messageIds[]" value="<?= htmlspecialchars($mid); ?>"></td>
                                                        <td class="text-truncate" style="max-width:180px"><?= htmlspecialchars($m['fromAddress'] ?? ($m['sender'] ?? '-')); ?></td>
                                                        <td class="text-truncate" style="max-width:420px">
                                                            <a href="<?= base_url('ZohoMail/view/' . rawurlencode($fid) . '/' . rawurlencode($mid)); ?>">
                                                                <?= htmlspecialchars($m['subject'] ?? '(no subject)'); ?>
                                                            </a>
                                                            <?php if (!empty($m['hasAttachment'])): ?>
                                                                <i class="mdi mdi-paperclip text-muted ml-1"></i>
                                                            <?php endif; ?>
                                                            <small class="text-muted d-block text-truncate"><?= htmlspecialchars($m['summary'] ?? ''); ?></small>
                                                        </td>
                                                        <td><small class="text-muted"><?= $tsLabel; ?></small></td>
                                                    </tr>
                                                <?php endforeach; endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>

                                <div class="d-flex justify-content-between mt-2">
                                    <?php $prev = max(1, $start - 25); $next = $start + 25; ?>
                                    <a class="btn btn-sm btn-outline-secondary <?= $start<=1?'disabled':''; ?>"
                                        href="<?= base_url('ZohoMail/inbox?'.http_build_query(array_filter(['folderId'=>$currentFolder,'search'=>$search,'start'=>$prev]))); ?>">
                                        <i class="mdi mdi-chevron-left"></i> Prev
                                    </a>
                                    <span class="text-muted small align-self-center">Showing from #<?= $start; ?></span>
                                    <a class="btn btn-sm btn-outline-secondary <?= count($messages)<25?'disabled':''; ?>"
                                        href="<?= base_url('ZohoMail/inbox?'.http_build_query(array_filter(['folderId'=>$currentFolder,'search'=>$search,'start'=>$next]))); ?>">
                                        Next <i class="mdi mdi-chevron-right"></i>
                                    </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <?php include(APPPATH.'views/includes/footer.php'); ?>
    </div>
</div>
<?php include(APPPATH.'views/includes/themecustomizer.php'); ?>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
<script>
document.getElementById('selectAll')?.addEventListener('change', function(e){
    document.querySelectorAll('input[name="messageIds[]"]').forEach(cb => cb.checked = e.target.checked);
});
</script>
</body>
</html>
