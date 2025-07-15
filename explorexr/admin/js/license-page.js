/**
 * ExploreXR License Page Script
 * Handles license activation, addon selection, and tier transitions
 */

jQuery(document).ready(function($) {
    console.log('ExpoXR License Page: Script loaded successfully');
    
    // Show license message function with improved UI feedback
    function showLicenseMessage(message, type, targetElement) {
        if (!targetElement || targetElement.length === 0) {
            // Fallback: try to find any validation result element
            targetElement = $('.expoxr-license-validation-result').first();
            if (targetElement.length === 0) {
                // Ultimate fallback: create a temporary div and insert it after the license input
                var licenseInput = $('input[name="license_key"]');
                if (licenseInput.length > 0) {
                    targetElement = $('<div class="expoxr-license-validation-result"></div>');
                    licenseInput.after(targetElement);
                } else {
                    return;
                }
            }
        }
        
        var messageClass = 'notice notice-' + type;
        var icon = '';
        
        switch(type) {
            case 'success':
                icon = '<span class="dashicons dashicons-yes-alt"></span>';
                break;
            case 'error':
                icon = '<span class="dashicons dashicons-dismiss"></span>';
                break;
            case 'warning':
                icon = '<span class="dashicons dashicons-warning"></span>';
                break;
            case 'info':
                icon = '<span class="dashicons dashicons-info"></span>';
                break;
            default:
                icon = '<span class="dashicons dashicons-info"></span>';
        }
        
        var messageHtml = '<div class="' + messageClass + ' inline" style="display: block !important; margin: 10px 0;">' + 
                         '<p style="margin: 0; padding: 0;">' + icon + ' ' + message + '</p>' + 
                         '</div>';
        
        // Clear any existing content and set new message
        targetElement.html(messageHtml);
        
        // Force show the element with multiple methods
        targetElement.attr('style', 'display: block !important; visibility: visible !important;');
        targetElement.show();
        targetElement.removeClass('hidden');
        
        // Auto-hide success and info messages after 5 seconds
        if (type === 'success' || type === 'info') {
            setTimeout(function() {
                targetElement.fadeOut();
            }, 5000);
        }
    }
    
    
    // Get current license key (helper function)
    function getCurrentLicenseKey() {
        // Check if there's a stored license key in a data attribute or global variable
        if (typeof expoXRLicense !== 'undefined' && expoXRLicense.currentLicenseKey) {
            return expoXRLicense.currentLicenseKey;
        }
        return '';
    }
    
    // Copy test license key to clipboard
    $('.copy-test-key').on('click', function() {
        var key = $(this).data('key');
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(key).select();
        document.execCommand('copy');
        tempInput.remove();
        
        // Show feedback
        var originalText = $(this).text();
        $(this).text('Copied!');
        
        // Reset button text after 2 seconds
        setTimeout(function(btn) {
            $(btn).text(originalText);
        }, 2000, this);
        
        // Auto-fill the license input if it exists
        if ($('input[name="license_key"]').length) {
            $('input[name="license_key"]').val(key);
        }
    });
    
    // Handle tier upgrade buttons
    $('.tier-upgrade-btn, .tier-downgrade-btn, .tier-change-btn').on('click', function(e) {
        e.preventDefault();
        
        var tier = $(this).data('tier');
        var currentTier = expoXRLicense.currentTier;
        var message;
        
        if (tier === 'ultra') {
            message = 'Upgrading to Ultra will give you access to all add-ons.';
        } else if (tier === 'plus' && currentTier === 'pro') {
            message = 'Upgrading to Plus will allow you to select up to 4 add-ons.';
        } else if (tier === 'pro' && currentTier === 'plus') {
            message = 'Downgrading to Pro will limit you to 2 add-ons. Your current add-on selections may be adjusted.';
        } else if ((tier === 'pro' || tier === 'plus') && currentTier === 'ultra') {
            message = 'Downgrading from Ultra will require you to select specific add-ons. Your previous selections will be restored if possible.';
        } else {
            var tierValues = {'free': 0, 'pro': 1, 'plus': 2, 'ultra': 3};
            var action = tierValues[tier] > tierValues[currentTier] ? 'Upgrading' : 
                         tierValues[tier] < tierValues[currentTier] ? 'Downgrading' : 'Changing';
            
            message = action + ' to the ' + tier.charAt(0).toUpperCase() + tier.slice(1) + ' tier.';
        }
        
        var tierValues = {'free': 0, 'pro': 1, 'plus': 2, 'ultra': 3};
        var actionType = tierValues[tier] > tierValues[currentTier] ? 'upgrade' : 
                     tierValues[tier] < tierValues[currentTier] ? 'downgrade' : 'change';
        
        if (confirm('Continue to ' + actionType + '?\n\n' + message)) {
            window.location.href = 'https://expoxr.com/explorexr/pricing?tier=' + tier;
        }
    });
    // Validate license in real-time
    $('.expoxr-validate-license').on('click', function(e) {
        e.preventDefault();
        
        var form = $(this).closest('form');
        var licenseKey = form.find('input[name="license_key"]').val();
        var resultDiv = form.find('.expoxr-license-validation-result');
        var activateBtn = form.find('.expoxr-activate-license');
        var validateBtn = $(this);
        var nonce = form.find('input[name="expoxr_license_nonce"]').val();
        
        if (!licenseKey) {
            showLicenseMessage('Please enter a license key to validate.', 'warning', resultDiv);
            activateBtn.hide();
            return;
        }
        
        validateBtn.prop('disabled', true).text('Validating...');
        resultDiv.hide();
        activateBtn.hide();
        
        $.ajax({
            url: expoXRLicense.ajaxUrl,
            type: 'POST',
            data: {
                action: 'expoxr_validate_license_real_time',
                license_key: licenseKey,
                expoxr_license_nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var message = data.message;
                    var messageType = data.type || 'success';
                    
                    if (data.already_activated) {
                        showLicenseMessage(message, 'success', resultDiv);
                        validateBtn.text('Already Activated').prop('disabled', true);
                    } else {
                        showLicenseMessage(message, 'info', resultDiv);
                        validateBtn.text('Activate License').removeClass('button-primary').addClass('button-secondary').prop('disabled', false);
                        activateBtn.show();
                        
                        // Change the validate button to activation button
                        validateBtn.off('click').on('click', function(e) {
                            activateLicense(e, form, licenseKey, nonce, resultDiv, validateBtn);
                        });
                    }
                } else {
                    var errorType = response.data ? response.data.type : 'error';
                    showLicenseMessage(response.data.message, errorType, resultDiv);
                    validateBtn.prop('disabled', false).text('Validate License');
                    activateBtn.hide();
                }
            },
            error: function(xhr, status, error) {
                showLicenseMessage('An error occurred while validating the license. Please try again.', 'error', resultDiv);
                validateBtn.prop('disabled', false).text('Validate License');
                activateBtn.hide();
            }
        });
    });
    
    // Function to handle license activation
    function activateLicense(e, form, licenseKey, nonce, resultDiv, button) {
        e.preventDefault();
        
        button.prop('disabled', true).text('Activating...');
        
        $.ajax({
            url: expoXRLicense.ajaxUrl,
            type: 'POST',
            data: {
                action: 'expoxr_activate_license',
                license_key: licenseKey,
                expoxr_license_nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    showLicenseMessage(response.data.message, 'success', resultDiv);
                    // Reload page after successful activation
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    showLicenseMessage(response.data.message, 'error', resultDiv);
                    button.prop('disabled', false).text('Activate License');
                }
            },
            error: function(xhr, status, error) {
                showLicenseMessage('An error occurred while activating the license. Please try again.', 'error', resultDiv);
                button.prop('disabled', false).text('Activate License');
            }
        });
    }
    
    // Validate current license (re-validate button)
    $('.expoxr-validate-current-license').on('click', function(e) {
        e.preventDefault();
        
        var form = $(this).closest('form');
        var resultDiv = form.find('.expoxr-license-validation-result');
        var licenseKey = getCurrentLicenseKey();
        
        if (!licenseKey) {
            showLicenseMessage('No license key found to validate.', 'warning', resultDiv);
            return;
        }
        
        $(this).prop('disabled', true).text('Validating...');
        resultDiv.hide();
        
        $.ajax({
            url: expoXRLicense.ajaxUrl,
            type: 'POST',
            data: {
                action: 'expoxr_validate_license_real_time',
                license_key: licenseKey,
                expoxr_license_nonce: form.find('input[name="expoxr_license_nonce"]').val()
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    showLicenseMessage(data.message, data.type || 'success', resultDiv);
                } else {
                    var errorType = response.data ? response.data.type : 'error';
                    showLicenseMessage(response.data.message, errorType, resultDiv);
                }
                $('.expoxr-validate-current-license').prop('disabled', false).text('Re-validate License');
            },
            error: function() {
                showLicenseMessage('An error occurred while validating the license. Please try again.', 'error', resultDiv);
                $('.expoxr-validate-current-license').prop('disabled', false).text('Re-validate License');
            }
        });
    });
    
    // Handle license activation
    $('.expoxr-activate-license').on('click', function(e) {
        e.preventDefault();
        
        var form = $(this).closest('form');
        var licenseKey = form.find('input[name="license_key"]').val();
        var resultDiv = form.find('.expoxr-license-validation-result');
        var nonce = form.find('input[name="expoxr_license_nonce"]').val();
        
        if (!licenseKey) {
            showLicenseMessage('Please enter a license key to activate.', 'warning', resultDiv);
            return;
        }
        
        $(this).prop('disabled', true).text('Activating...');
        
        $.ajax({
            url: expoXRLicense.ajaxUrl,
            type: 'POST',
            data: {
                action: 'expoxr_activate_license',
                license_key: licenseKey,
                expoxr_license_nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    showLicenseMessage(response.data.message, 'success', resultDiv);
                    // Reload page after successful activation
                    setTimeout(function() {
                        window.location.reload();
                    }, 2000);
                } else {
                    showLicenseMessage(response.data.message, 'error', resultDiv);
                    $('.expoxr-activate-license').prop('disabled', false).text('Activate License');
                }
            },
            error: function(xhr, status, error) {
                showLicenseMessage('An error occurred while activating the license. Please try again.', 'error', resultDiv);
                $('.expoxr-activate-license').prop('disabled', false).text('Activate License');
            }
        });
    });
    
    // Deactivate license
    $('.expoxr-deactivate-license').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to deactivate your license? This will disable premium features and add-ons.')) {
            return;
        }
        
        var form = $(this).closest('form');
        var resultDiv = form.find('.expoxr-license-validation-result');
        
        $(this).prop('disabled', true).text('Deactivating...');
        resultDiv.hide();
        
        $.ajax({
            url: expoXRLicense.ajaxUrl,
            type: 'POST',
            data: {
                action: 'expoxr_deactivate_license_improved',
                expoxr_license_nonce: form.find('input[name="expoxr_license_nonce"]').val()
            },
            success: function(response) {
                if (response.success) {
                    showLicenseMessage(response.data.message, 'success', resultDiv);
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showLicenseMessage(response.data.message, 'error', resultDiv);
                    $('.expoxr-deactivate-license').prop('disabled', false).text('Deactivate License');
                }
            },
            error: function() {
                showLicenseMessage('An error occurred while deactivating the license. Please try again.', 'error', resultDiv);
                $('.expoxr-deactivate-license').prop('disabled', false).text('Deactivate License');
            }
        });
    });
    
    // Save add-on selections
    $('.expoxr-save-addons').on('click', function(e) {
        e.preventDefault();
        
        var form = $(this).closest('form');
        var selectedAddons = [];
        var resultDiv = $('.expoxr-license-validation-result');
        
        form.find('input[name="addon_ids[]"]:checked').each(function() {
            selectedAddons.push($(this).val());
        });
        
        $(this).prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: expoXRLicense.ajaxUrl,
            type: 'POST',
            data: {
                action: 'expoxr_select_addon',
                addon_ids: selectedAddons,
                expoxr_license_nonce: form.find('input[name="expoxr_license_nonce"]').val()
            },
            success: function(response) {
                if (response.success) {
                    showLicenseMessage(response.data.message, 'success', resultDiv);
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showLicenseMessage(response.data.message, 'error', resultDiv);
                    $('.expoxr-save-addons').prop('disabled', false).text('Save Add-on Selections');
                }
            },
            error: function() {
                showLicenseMessage('An error occurred while saving addon selections. Please try again.', 'error', resultDiv);
                $('.expoxr-save-addons').prop('disabled', false).text('Save Add-on Selections');
            }
        });
    });
    
    // Add-on checkbox handling for all tiers (including free)
    if (typeof expoXRLicense !== 'undefined' && expoXRLicense.currentTier !== 'ultra') {
        var maxAddons = expoXRLicense.currentTier === 'free' ? 1 : expoXRLicense.tiers[expoXRLicense.currentTier].max_addons;
        
        $('input[name="addon_ids[]"]').on('change', function() {
            var checkedCount = $('input[name="addon_ids[]"]:checked').length;
            var resultDiv = $('.expoxr-license-validation-result');
            
            if (checkedCount > maxAddons) {
                $(this).prop('checked', false);
                showLicenseMessage('You can only select up to ' + maxAddons + ' add-on(s) with your ' + expoXRLicense.currentTier.charAt(0).toUpperCase() + expoXRLicense.currentTier.slice(1) + ' license. Please upgrade to select more add-ons.', 'warning', resultDiv);
                return;
            }
            
            if (checkedCount >= maxAddons) {
                $('input[name="addon_ids[]"]:not(:checked)').closest('.expoxr-addon-card').addClass('disabled');
            } else {
                $('.expoxr-addon-card').removeClass('disabled');
            }
        });
    }
});
