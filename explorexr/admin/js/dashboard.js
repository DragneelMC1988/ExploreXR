/**
 * ExpoXR Admin Dashboard JavaScript
 * Handles functionality for the ExpoXR dashboard
 */
jQuery(document).ready(function($) {
    
    // Premium banner dismiss functionality
    $('.expoxr-pro-banner .expoxr-banner-dismiss').on('click', function(e) {
        e.preventDefault();
        const banner = $(this).closest('.expoxr-pro-banner');
        
        // Animate banner dismissal
        banner.addClass('banner-dismissing');
        
        setTimeout(function() {
            banner.remove();
        }, 300);
        
        // Send AJAX request to hide banner for this session
        if (expoxr_dashboard && expoxr_dashboard.ajax_url && expoxr_dashboard.nonce) {
            $.ajax({
                url: expoxr_dashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'expoxr_dismiss_premium_banner',
                    nonce: expoxr_dashboard.nonce
                },
                success: function(response) {
                    console.log('Premium banner dismissed for this session');
                },
                error: function(xhr, status, error) {
                    console.log('Error dismissing banner:', error);
                }
            });
        }
    });
    
    // Copy shortcode functionality
    $('.copy-shortcode').on('click', function() {
        const shortcode = $(this).data('shortcode');
        
        // Use Clipboard API to copy text
        navigator.clipboard.writeText(shortcode).then(function() {
            // Show success notification
            const notification = $('#expoxr-copied-notification');
            notification.fadeIn(300);
            
            // Hide notification after 2 seconds
            setTimeout(function() {
                notification.fadeOut(300);
            }, 2000);
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = shortcode;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            // Show success notification
            const notification = $('#expoxr-copied-notification');
            notification.fadeIn(300);
            setTimeout(function() {
                notification.fadeOut(300);
            }, 2000);
        });
    });
    
    // Model viewer modal functionality
    const modal = $('#expoxr-model-modal');
    const modelViewer = $('#expoxr-model-viewer');
    const modelTitle = $('#expoxr-model-title');
    
    // Open modal when clicking View Model
    $('.view-3d-model').on('click', function(e) {
        e.preventDefault();
        console.log('View 3D Model button clicked');
        const modelUrl = $(this).data('model-url');
        const modelName = $(this).data('model-name');
        const posterUrl = $(this).data('poster-url');
        const animationName = $(this).data('animation-name');
        
        console.log('Loading 3D model from URL:', modelUrl);
        console.log('Modal element:', modal);
        console.log('Model viewer element:', modelViewer);
        
        // Reset any previous error messages
        $('.error-details').text('');
        
        // Register error handler before setting source
        if (modelViewer[0]) {
            modelViewer[0].addEventListener('error', function(event) {
                console.error('Model viewer error:', event);
                $('.error-details').text('Error type: ' + (event.detail?.type || 'unknown') + 
                                         ' - Path: ' + modelUrl);
            });
            
            // Add event listener for when model is successfully loaded
            modelViewer[0].addEventListener('load', function() {
                console.log('Model loaded successfully');
            });
        }
        
        // Update model viewer source and title
        modelViewer.attr('src', modelUrl);
        modelTitle.text('3D Model Preview: ' + modelName);
        
        console.log('Model viewer source set to:', modelViewer.attr('src'));
        console.log('Modal title set to:', modelTitle.text());
        
        // Add poster if available
        if (posterUrl) {
            modelViewer.attr('poster', posterUrl);
        } else {
            modelViewer.removeAttr('poster');
        }
        
        // Enable single animation if available (Free Version feature)
        if (animationName) {
            modelViewer.attr('animation-name', animationName);
            modelViewer.attr('autoplay', '');
        } else {
            modelViewer.removeAttr('animation-name');
            modelViewer.removeAttr('autoplay');
        }
        
        // Configure basic camera controls (Free Version feature)
        modelViewer.attr('camera-controls', '');
        modelViewer.attr('camera-orbit', '0deg 75deg 105%');
        
        // Add loading UI options (Free Version feature)
        modelViewer.attr('data-loading-display', 'both');
        
        console.log('About to show modal');
        // Show modal
        modal.css('display', 'block');
        console.log('Modal should now be visible');
    });
    
    // Close modal
    $('.expoxr-model-close').on('click', function() {
        modal.css('display', 'none');
        modelViewer.attr('src', '');
        modelViewer.removeAttr('poster');
    });
    
    // Close modal when clicking outside of the content
    $(window).on('click', function(e) {
        if (e.target === modal[0]) {
            modal.css('display', 'none');
            modelViewer.attr('src', '');
            modelViewer.removeAttr('poster');
        }
    });
    
    // System status card interaction
    $('.expoxr-status-card').hover(
        function() {
            $(this).find('.expoxr-status-action').css('opacity', 1);
        },
        function() {
            $(this).find('.expoxr-status-action').css('opacity', 0.7);
        }
    );
    
    // Premium features interaction
    $('.expoxr-premium-card').hover(
        function() {
            $(this).find('.expoxr-premium-action').css('opacity', 1);
        },
        function() {
            $(this).find('.expoxr-premium-action').css('opacity', 0.7);
        }
    );
});