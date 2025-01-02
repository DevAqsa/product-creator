jQuery(document).ready(function($) {
    const form = $('#custom-product-form');
    const submitButton = form.find('button[type="submit"]');
    const noticeContainer = $('.notice-container');

    function showNotice(message, type) {
        const notice = $('<div>')
            .addClass(`notice notice-${type}`)
            .text(message);
        
        noticeContainer.html(notice);
        
        // Clear notice after 5 seconds
        setTimeout(() => {
            notice.fadeOut(() => notice.remove());
        }, 5000);
    }

    form.on('submit', function(e) {
        e.preventDefault();

        // Clear previous notices
        noticeContainer.empty();
        
        // Disable submit button
        submitButton.prop('disabled', true)
            .text('Creating product...');

        const formData = new FormData(this);
        formData.append('action', 'create_custom_product');
        formData.append('nonce', customProductCreator.nonce);

        $.ajax({
            url: customProductCreator.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotice(response.data.message, 'success');
                    form[0].reset();
                    
                    // Add edit link
                    if (response.data.product_url) {
                        const editLink = $('<a>')
                            .attr('href', response.data.product_url)
                            .text('Edit product')
                            .addClass('button button-secondary')
                            .css('margin-left', '10px');
                        
                        submitButton.after(editLink);
                    }
                } else {
                    showNotice(response.data || 'An error occurred while creating the product.', 'error');
                }
            },
            error: function(xhr, status, error) {
                showNotice('An error occurred while creating the product: ' + error, 'error');
            },
            complete: function() {
                submitButton.prop('disabled', false)
                    .text('Create Product');
            }
        });
    });
});