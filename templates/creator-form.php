<div class="wrap">
    <h1><?php _e('Custom Product Creator', 'custom-product-creator'); ?></h1>
    
    <div class="notice-container"></div>

    <form id="custom-product-form" class="product-creator-form">
        <div class="form-field">
            <label for="product-title"><?php _e('Product Title', 'custom-product-creator'); ?></label>
            <input type="text" id="product-title" name="title" required>
        </div>

        <div class="form-field">
            <label for="product-description"><?php _e('Product Description', 'custom-product-creator'); ?></label>
            <textarea id="product-description" name="description" rows="5" required></textarea>
        </div>

        <div class="form-field">
            <label for="product-price"><?php _e('Regular Price ($)', 'custom-product-creator'); ?></label>
            <input type="number" id="product-price" name="price" step="0.01" min="0" required>
        </div>

        <div class="form-field">
            <label for="product-sku"><?php _e('SKU', 'custom-product-creator'); ?></label>
            <input type="text" id="product-sku" name="sku" required>
        </div>

        <div class="form-field">
            <label for="product-stock"><?php _e('Stock Quantity', 'custom-product-creator'); ?></label>
            <input type="number" id="product-stock" name="stock" min="0" required>
        </div>

        <button type="submit" class="button button-primary submit-button">
            <?php _e('Create Product', 'custom-product-creator'); ?>
        </button>
    </form>
</div>