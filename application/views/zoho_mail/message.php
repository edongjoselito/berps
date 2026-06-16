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
                    <a href="<?= base_url('ZohoMail/inbox'); ?>" class="btn btn-sm btn-outline-secondary"><i class="mdi mdi-arrow-left"></i> Back to Inbox</a>
                    <div>
                        <a href="<?= base_url('ZohoMail/compose?to='.rawurlencode($meta['fromAddress'] ?? '').'&subject='.rawurlencode('Re: '.($meta['subject'] ?? ''))); ?>"
                           class="btn btn-sm btn-primary"><i class="mdi mdi-reply"></i> Reply</a>
                    </div>
                </div>

                <?php if (!empty($apiError)): ?>
                    <div class="alert alert-warning"><strong>API:</strong> <?= htmlspecialchars($apiError); ?></div>
                <?php endif; ?>

                <?php if ($meta): ?>
                    <div class="card">
                        <div class="card-body">
                            <h4 class="mb-2"><?= htmlspecialchars($meta['subject'] ?? '(no subject)'); ?></h4>
                            <p class="mb-1"><strong>From:</strong> <?= htmlspecialchars($meta['fromAddress'] ?? '-'); ?></p>
                            <p class="mb-1"><strong>To:</strong> <?= htmlspecialchars($meta['toAddress'] ?? '-'); ?></p>
                            <?php if (!empty($meta['ccAddress'])): ?>
                                <p class="mb-1"><strong>Cc:</strong> <?= htmlspecialchars($meta['ccAddress']); ?></p>
                            <?php endif; ?>
                            <p class="text-muted small mb-3">
                                <?php
                                $ts = $meta['receivedTime'] ?? ($meta['sentDateInGMT'] ?? null);
                                if ($ts) echo htmlspecialchars(date('M j, Y g:i a', is_numeric($ts) ? ((int)$ts/1000) : strtotime($ts)));
                                ?>
                            </p>
                            <hr>

                            <div class="mail-content" style="overflow:auto; max-width:100%;">
                                <?php
                                $html = $content['content'] ?? ($content ?? '');
                                if (is_array($html)) $html = $html['content'] ?? '';
                                echo $html;
                                ?>
                            </div>

                            <?php if (!empty($meta['attachments']) && is_array($meta['attachments'])): ?>
                                <hr>
                                <h6><i class="mdi mdi-paperclip"></i> Attachments</h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($meta['attachments'] as $att):
                                        $aid = $att['attachmentId'] ?? ($att['attachmentName'] ?? '');
                                        $name = $att['attachmentName'] ?? 'attachment';
                                    ?>
                                        <li>
                                            <a href="<?= base_url('ZohoMail/attachment/'.rawurlencode($folderId).'/'.rawurlencode($messageId).'/'.rawurlencode($aid).'/'.rawurlencode($name)); ?>">
                                                <i class="mdi mdi-download"></i> <?= htmlspecialchars($name); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary">Message not found.</div>
                <?php endif; ?>

            </div>
        </div>
        <?php include(APPPATH.'views/includes/footer.php'); ?>
    </div>
</div>
<?php include(APPPATH.'views/includes/themecustomizer.php'); ?>
<script src="<?= base_url(); ?>assets/js/vendor.min.js"></script>
<script src="<?= base_url(); ?>assets/js/app.min.js"></script>
</body>
</html>
