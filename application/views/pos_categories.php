<!DOCTYPE html>
<html lang="en">
<?php include('includes/head.php'); ?>

<body>
    <div id="wrapper">
        <?php include('includes/top-nav-bar.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="content-page">
            <div class="content">
                <div class="container-fluid pos-staff-dashboard pb-4">
                    <style>
                        .pos-staff-dashboard .page-title-box {
                            padding: 6px 0 8px;
                            margin: 0 0 12px;
                        }

                        .pos-staff-dashboard .page-title {
                            font-weight: 700;
                            letter-spacing: -0.2px;
                            margin-bottom: 4px;
                        }

                        .pos-staff-dashboard .table thead th {
                            white-space: nowrap;
                        }

                        /* Hero Banner */
                        .pos-staff-dashboard .pc-hero {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            flex-wrap: wrap;
                            gap: 16px;
                            padding: 28px 24px;
                            margin: 0 0 22px;
                            border-radius: 16px;
                            background: #b45309;
                            box-shadow: 0 8px 32px rgba(180, 83, 9, 0.25);
                            position: relative;
                            overflow: hidden;
                        }

                        .pos-staff-dashboard .pc-hero::before {
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

                        .pos-staff-dashboard .pc-hero::after {
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

                        .pos-staff-dashboard .pc-hero__content {
                            position: relative;
                            z-index: 1;
                        }

                        .pos-staff-dashboard .pc-hero__eyebrow {
                            display: inline-flex;
                            align-items: center;
                            gap: 6px;
                            margin-bottom: 8px;
                            color: rgba(255, 255, 255, 0.85);
                            font-size: 0.78rem;
                            font-weight: 600;
                            letter-spacing: 0.04em;
                        }

                        .pos-staff-dashboard .pc-hero__eyebrow i {
                            font-size: 1rem;
                        }

                        .pos-staff-dashboard .pc-hero__title {
                            margin: 0 0 4px 0;
                            color: #fff;
                            font-size: clamp(1.6rem, 2.5vw, 2.2rem);
                            font-weight: 800;
                            line-height: 1.15;
                            letter-spacing: -0.02em;
                            font-family: var(--font-primary, Montserrat, Segoe UI, Arial, sans-serif), "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
                        }

                        .pos-staff-dashboard .pc-hero__subtitle {
                            margin: 0;
                            color: rgba(255, 255, 255, 0.8);
                            font-size: 0.88rem;
                            max-width: 520px;
                        }

                        .pos-staff-dashboard .pc-hero__actions {
                            display: flex;
                            align-items: center;
                            flex-wrap: wrap;
                            gap: 10px;
                            position: relative;
                            z-index: 1;
                        }

                        .pos-staff-dashboard .pc-hero-btn {
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

                        .pos-staff-dashboard .pc-hero-btn:hover,
                        .pos-staff-dashboard .pc-hero-btn:focus {
                            background: rgba(255, 255, 255, 0.25);
                            border-color: rgba(255, 255, 255, 0.5);
                            color: #fff;
                            text-decoration: none;
                            transform: translateY(-1px);
                        }

                        /* Tag swing animation */
                        .pos-staff-dashboard .tag-swing {
                            display: inline-block;
                            animation: tag-swing 2.5s ease-in-out infinite;
                            transform-origin: top center;
                        }

                        @keyframes tag-swing {
                            0%, 70%, 100% { transform: rotate(0deg); }
                            15% { transform: rotate(-15deg); }
                            30% { transform: rotate(10deg); }
                            45% { transform: rotate(-6deg); }
                            60% { transform: rotate(0deg); }
                        }

                        /* Amber accent on cards */
                        .pos-staff-dashboard .card {
                            border-top: 3px solid #b45309;
                        }

                        .pos-staff-dashboard .card .card-header {
                            border-bottom: 2px solid #b45309;
                        }

                        /* Responsive hero */
                        @media (max-width: 767px) {
                            .pos-staff-dashboard .pc-hero,
                            .pos-staff-dashboard .pc-hero__actions {
                                flex-direction: column;
                                align-items: stretch;
                            }

                            .pos-staff-dashboard .pc-hero {
                                padding: 20px;
                            }

                            .pos-staff-dashboard .pc-hero-btn {
                                flex: 1 1 auto;
                                justify-content: center;
                            }
                        }
                    </style>

                    <div class="pc-hero">
                        <div class="pc-hero__content">
                            <div class="pc-hero__eyebrow">
                                <i class="mdi mdi-tag-multiple-outline"></i>
                                Product Organization
                            </div>
                            <h1 class="pc-hero__title"><?= htmlspecialchars($page_title ?? 'POS Categories', ENT_QUOTES, 'UTF-8'); ?> <span class="tag-swing">🏷️</span></h1>
                            <p class="pc-hero__subtitle"><?= htmlspecialchars($page_subtitle ?? 'Organize your POS products into categories for easier browsing and reporting.', ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="pc-hero__actions">
                            <a class="pc-hero-btn" href="<?= base_url(); ?>Pos/posProductList">
                                <i class="mdi mdi-arrow-left"></i>
                                <span>Back to Products</span>
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($notice)): ?>
                        <div class="alert alert-<?= ($notice_type === 'error') ? 'danger' : 'success'; ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="<?= base_url('Pos/posCategoryCreate'); ?>" class="mb-3">
                                <div class="form-row">
                                    <div class="form-group col-md-8 mb-2">
                                        <label for="newCategoryName" class="mb-1"><strong>New Category</strong></label>
                                        <input type="text" id="newCategoryName" name="name" class="form-control" required>
                                    </div>
                                    <div class="form-group col-md-4 mb-2 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary btn-block">Add Category</button>
                                    </div>
                                </div>
                            </form>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:80px">#</th>
                                            <th>Name</th>
                                            <th style="width:220px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($categories)): ?>
                                            <?php $row = 1; foreach ($categories as $cat): ?>
                                                <tr>
                                                    <td><?= $row++; ?></td>
                                                    <td>
                                                        <form method="post" action="<?= base_url('Pos/posCategoryUpdate'); ?>" class="d-flex align-items-center" style="gap:8px;">
                                                            <input type="hidden" name="id" value="<?= (int) ($cat->id ?? 0); ?>">
                                                            <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($cat->name ?? ''), ENT_QUOTES, 'UTF-8'); ?>" required>
                                                            <button type="submit" class="btn btn-outline-primary btn-sm">Save</button>
                                                        </form>
                                                    </td>
                                                    <td>
                                                        <a href="<?= base_url('Pos/posCategoryDelete?id=' . (int) ($cat->id ?? 0)); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this category?');">Delete</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">No categories yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
