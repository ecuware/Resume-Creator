<?php
// Content management page
$resume = json_decode(file_get_contents(__DIR__ . '/src/resume.json'), true);
?>

<!-- Header Editing -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fa fa-user"></i> Personal Information</h3>
    </div>
    <div class="card-body">
        <form id="headerForm" class="form-grid">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($resume['header']['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="subtitle" value="<?php echo htmlspecialchars($resume['header']['subtitle'] ?? ''); ?>" required>
            </div>
        </form>
        <div class="form-actions">
            <button onclick="saveHeader()" class="btn btn-primary">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- Contact Information -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fa fa-address-book"></i> Contact Information</h3>
    </div>
    <div class="card-body">
        <div id="navItems">
            <?php foreach (($resume['header']['nav'] ?? []) as $index => $nav): ?>
                <div class="nav-item-row" data-index="<?php echo $index; ?>">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="label" value="<?php echo htmlspecialchars($nav['label'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Icon</label>
                            <select name="icon">
                                <option value="envelope" <?php echo ($nav['icon'] ?? '') === 'envelope' ? 'selected' : ''; ?>>Email</option>
                                <option value="github" <?php echo ($nav['icon'] ?? '') === 'github' ? 'selected' : ''; ?>>GitHub</option>
                                <option value="linkedin" <?php echo ($nav['icon'] ?? '') === 'linkedin' ? 'selected' : ''; ?>>LinkedIn</option>
                                <option value="pin" <?php echo ($nav['icon'] ?? '') === 'pin' ? 'selected' : ''; ?>>Location</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Link (Optional)</label>
                            <input type="text" name="href" value="<?php echo htmlspecialchars($nav['href'] ?? ''); ?>" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="button" onclick="removeNavItem(<?php echo $index; ?>)" class="btn btn-danger btn-sm">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions">
            <button onclick="addNavItem()" class="btn btn-secondary">
                <i class="fa fa-plus"></i> Add New
            </button>
            <button onclick="saveNav()" class="btn btn-primary">
                <i class="fa fa-save"></i> Save
            </button>
        </div>
    </div>
</div>

<!-- Left Column Sections -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fa fa-align-left"></i> Left Column Sections</h3>
    </div>
    <div class="card-body">
        <div id="leftSections">
            <?php foreach (($resume['left'] ?? []) as $sectionIndex => $section): ?>
                <div class="section-card" data-section-index="<?php echo $sectionIndex; ?>" data-side="left">
                    <div class="section-header">
                        <h4><i class="fa fa-folder"></i> <?php echo htmlspecialchars($section['name'] ?? 'Section'); ?></h4>
                        <div class="section-actions">
                            <button onclick="editSection(<?php echo $sectionIndex; ?>, 'left')" class="btn btn-sm btn-secondary">
                                <i class="fa fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteSection(<?php echo $sectionIndex; ?>, 'left')" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <div class="section-items">
                        <?php foreach (($section['items'] ?? []) as $itemIndex => $item): ?>
                            <div class="item-preview">
                                <strong><?php echo htmlspecialchars($item['title'] ?? ''); ?></strong>
                                <?php if (!empty($item['subtitle'])): ?>
                                    <span class="text-muted"> - <?php echo htmlspecialchars($item['subtitle']); ?></span>
                                <?php endif; ?>
                                <div>
                                    <button onclick="editItem(<?php echo $sectionIndex; ?>, <?php echo $itemIndex; ?>, 'left')" class="btn btn-xs btn-link">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button onclick="deleteItem(<?php echo $sectionIndex; ?>, <?php echo $itemIndex; ?>, 'left')" class="btn btn-xs btn-link" style="color: #dc3545;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button onclick="addItem(<?php echo $sectionIndex; ?>, 'left')" class="btn btn-sm btn-secondary" style="margin-top: 8px; width: 100%;">
                            <i class="fa fa-plus"></i> Add New Item
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions">
            <button onclick="addSection('left')" class="btn btn-secondary">
                <i class="fa fa-plus"></i> Add New Section
            </button>
        </div>
    </div>
</div>

<!-- Right Column Sections -->
<div class="card mb-3">
    <div class="card-header">
        <h3><i class="fa fa-align-right"></i> Right Column Sections</h3>
    </div>
    <div class="card-body">
        <div id="rightSections">
            <?php foreach (($resume['right'] ?? []) as $sectionIndex => $section): ?>
                <div class="section-card" data-section-index="<?php echo $sectionIndex; ?>" data-side="right">
                    <div class="section-header">
                        <h4><i class="fa fa-folder"></i> <?php echo htmlspecialchars($section['name'] ?? 'Section'); ?></h4>
                        <div class="section-actions">
                            <button onclick="editSection(<?php echo $sectionIndex; ?>, 'right')" class="btn btn-sm btn-secondary">
                                <i class="fa fa-edit"></i> Edit
                            </button>
                            <button onclick="deleteSection(<?php echo $sectionIndex; ?>, 'right')" class="btn btn-sm btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                    <div class="section-items">
                        <?php foreach (($section['items'] ?? []) as $itemIndex => $item): ?>
                            <div class="item-preview">
                                <strong><?php echo htmlspecialchars($item['title'] ?? ''); ?></strong>
                                <?php if (!empty($item['subtitle'])): ?>
                                    <span class="text-muted"> - <?php echo htmlspecialchars($item['subtitle']); ?></span>
                                <?php endif; ?>
                                <div>
                                    <button onclick="editItem(<?php echo $sectionIndex; ?>, <?php echo $itemIndex; ?>, 'right')" class="btn btn-xs btn-link">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button onclick="deleteItem(<?php echo $sectionIndex; ?>, <?php echo $itemIndex; ?>, 'right')" class="btn btn-xs btn-link" style="color: #dc3545;">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <button onclick="addItem(<?php echo $sectionIndex; ?>, 'right')" class="btn btn-sm btn-secondary" style="margin-top: 8px; width: 100%;">
                            <i class="fa fa-plus"></i> Add New Item
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="form-actions">
            <button onclick="addSection('right')" class="btn btn-secondary">
                <i class="fa fa-plus"></i> Add New Section
            </button>
        </div>
    </div>
</div>

<!-- Modal: Section Edit -->
<div id="sectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Edit Section</h3>
            <span class="modal-close" onclick="closeModal('sectionModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="sectionForm">
                <input type="hidden" id="sectionIndex" name="sectionIndex">
                <input type="hidden" id="sectionSide" name="sectionSide">
                <div class="form-group">
                    <label>Section Name *</label>
                    <input type="text" id="sectionName" name="name" required>
                </div>
                <div class="form-actions">
                    <button type="button" onclick="closeModal('sectionModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Item Edit -->
<div id="itemModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="itemModalTitle">Edit Item</h3>
            <span class="modal-close" onclick="closeModal('itemModal')">&times;</span>
        </div>
        <div class="modal-body">
            <form id="itemForm">
                <input type="hidden" id="itemSectionIndex" name="sectionIndex">
                <input type="hidden" id="itemIndex" name="itemIndex">
                <input type="hidden" id="itemSide" name="side">
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" id="itemTitle" name="title" required>
                </div>
                <div class="form-group">
                    <label>Subtitle</label>
                    <input type="text" id="itemSubtitle" name="subtitle">
                </div>
                <div class="form-group">
                    <label>Link</label>
                    <input type="text" id="itemHref" name="href" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>Upper Info</label>
                    <input type="text" id="itemUpper" name="upper" placeholder="e.g., Location, Company">
                </div>
                <div class="form-group">
                    <label>Lower Info</label>
                    <input type="text" id="itemLower" name="lower" placeholder="e.g., Date range">
                </div>
                <div class="form-group">
                    <label>Bullet Points (One per line)</label>
                    <textarea id="itemBullets" name="bullets" rows="5" placeholder="Write one bullet per line. Use *bold* for bold text."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" onclick="closeModal('itemModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
