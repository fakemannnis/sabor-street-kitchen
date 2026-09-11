<?php

?>
<form method="post" novalidate class="form-grid">
  <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>" />

  <div class="field">
    <label for="name">Item name <span class="required">*</span></label>
    <input type="text" id="name" name="name" required minlength="2" value="<?= h($values['name']) ?>" />
  </div>

  <div class="form-row">
    <div class="field">
      <label for="category_id">Category <span class="required">*</span></label>
      <select id="category_id" name="category_id" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= (int) $cat['id'] ?>" <?= (string) $cat['id'] === (string) $values['category_id'] ? 'selected' : '' ?>><?= h($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field">
      <label for="price">Price (AUD) <span class="required">*</span></label>
      <input type="number" id="price" name="price" required min="0" step="0.01" value="<?= h($values['price']) ?>" />
    </div>
  </div>

  <div class="field">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3"><?= h($values['description']) ?></textarea>
  </div>

  <div class="form-row">
    <div class="field">
      <label for="image">Image file</label>
      <select id="image" name="image">
        <?php foreach ($imageFiles as $file): ?>
          <option value="<?= h($file) ?>" <?= $file === $values['image'] ? 'selected' : '' ?>><?= h($file) ?></option>
        <?php endforeach; ?>
      </select>
      <p class="field-hint">Pick from images already in the <code>images/</code> folder.</p>
    </div>
    <div class="field">
      <label for="tags">Tags (comma separated)</label>
      <input type="text" id="tags" name="tags" placeholder="Spicy, Vegetarian" value="<?= h($values['tags']) ?>" />
    </div>
  </div>

  <div class="field" style="flex-direction:row; align-items:center; gap:0.6rem;">
    <input type="checkbox" id="is_featured" name="is_featured" value="1" <?= !empty($values['is_featured']) ? 'checked' : '' ?> style="width:auto;" />
    <label for="is_featured" style="text-transform:none; font-family:var(--font-body); font-size:1rem;">Show on homepage as a featured dish</label>
  </div>

  <div class="field" style="flex-direction:row; align-items:center; gap:0.6rem;">
    <input type="checkbox" id="is_available" name="is_available" value="1" <?= !empty($values['is_available']) ? 'checked' : '' ?> style="width:auto;" />
    <label for="is_available" style="text-transform:none; font-family:var(--font-body); font-size:1rem;">Available on the menu right now</label>
  </div>

  <button type="submit" class="btn btn-primary"><?= $mode === 'edit' ? 'Save Changes' : 'Add Item' ?></button>
</form>
