(function ($) {
    'use strict';

    $(document).ready(function () {

        // Coupon toggle handler
        $(document).on('click', '.hz-toggle-coupon-btn', function (e) {
            e.preventDefault();
            $('.hz-coupon-form-box').slideToggle(200);
        });

        // Apply Coupon AJAX
        $(document).on('click', '.hz-apply-coupon-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var couponCode = $('#hz_checkout_coupon_code').val().trim();
            var $msgBox = $('.hz-coupon-message');

            if (!couponCode) {
                $msgBox.removeClass('success').addClass('error').html('Please enter a coupon code.');
                return;
            }

            $btn.prop('disabled', true).text('...');
            $msgBox.removeClass('success error').html('');

            $.ajax({
                url: handzom_checkout_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'handzom_checkout_apply_coupon',
                    coupon_code: couponCode,
                    nonce: handzom_checkout_ajax.nonce
                },
                success: function (res) {
                    $btn.prop('disabled', false).text('APPLY');
                    if (res.success) {
                        $msgBox.addClass('success').html(res.data.message);
                        $('#hz_checkout_coupon_code').val('');
                        $('body').trigger('update_checkout');
                    } else {
                        $msgBox.addClass('error').html(res.data.message);
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).text('APPLY');
                    $msgBox.addClass('error').html('Error applying coupon. Please try again.');
                }
            });
        });

        // Remove Coupon Link
        $(document).on('click', '.hz-remove-coupon-link', function (e) {
            e.preventDefault();
            var couponCode = $(this).data('coupon');
            if (!couponCode) return;

            $.ajax({
                url: handzom_checkout_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'handzom_checkout_remove_coupon',
                    coupon_code: couponCode,
                    nonce: handzom_checkout_ajax.nonce
                },
                success: function (res) {
                    if (res.success) {
                        $('body').trigger('update_checkout');
                    }
                }
            });
        });

        // Toggle Ship to Different Address Visibility
        function updateShippingToggle() {
            var $checkbox = $('#ship-to-different-address-checkbox');
            if ($checkbox.length) {
                if ($checkbox.is(':checked')) {
                    $('.shipping_address').slideDown(200);
                } else {
                    $('.shipping_address').slideUp(200);
                }
            }
        }

        $(document).on('change', '#ship-to-different-address-checkbox', function () {
            updateShippingToggle();
        });

        // Initial check
        updateShippingToggle();

        // =========================================================================
        // Place Order Button Loading / Processing State
        // =========================================================================
        function setPlaceOrderLoading() {
            var $btn = $('#place_order');
            if ($btn.length && !$btn.hasClass('hz-loading')) {
                $btn.addClass('hz-loading');
                var currentText = $btn.text().trim();
                if (currentText && currentText !== 'PROCESSING...') {
                    $btn.data('original-text', currentText);
                }
                $btn.html('<span class="hz-btn-spinner" aria-hidden="true"></span><span class="hz-btn-text">PROCESSING...</span>');
                $btn.css('pointer-events', 'none');
            }
        }

        function resetPlaceOrderLoading() {
            var $btn = $('#place_order');
            if ($btn.length && $btn.hasClass('hz-loading')) {
                $btn.removeClass('hz-loading');
                var origText = $btn.data('original-text') || $btn.attr('data-value') || $btn.val() || 'PLACE ORDER';
                $btn.html(origText);
                $btn.css('pointer-events', '');
            }
        }

        // Trigger loading on checkout form submission
        $(document).on('checkout_place_order', function () {
            setPlaceOrderLoading();
            return true;
        });

        $('form.checkout').on('submit', function () {
            setPlaceOrderLoading();
        });

        // Scroll to top notices and reset button if checkout error occurs
        $(document.body).on('checkout_error', function () {
            resetPlaceOrderLoading();
            var $notices = $('.hz-checkout-notices');
            if ($notices.length) {
                $('html, body').animate({
                    scrollTop: $notices.offset().top - 100
                }, 400);
            }
        });

        $(document.body).on('updated_checkout', function () {
            resetPlaceOrderLoading();
        });

        // Reset registration fields, button state, and remove stale notices on pageshow / BFCache restore
        function resetRegistrationFormFields() {
            var $regForm = $('form.woocommerce-form-register, form.register, #hz-register-form');
            if ($regForm.length) {
                $regForm.data('hz-submitting', false);
                var $btn = $regForm.find('button[name="register"], .woocommerce-form-register__submit');
                $btn.removeClass('hz-loading').prop('disabled', false).css('pointer-events', '');
                if (!$btn.find('.hz-btn-text').length) {
                    $btn.text('CREATE ACCOUNT');
                } else {
                    $btn.html('CREATE ACCOUNT');
                }
                $regForm.find('#reg_email, #reg_password, #reg_password_confirmation, #reg_username, #reg_otp, #hz_reg_verification_token').val('');
                $regForm.find('.hz-password-input').attr('type', 'password');
                $regForm.find('.hz-password-toggle-btn').removeClass('is-showing-password').attr('aria-label', 'Show password').attr('aria-pressed', 'false');
                $regForm.find('.woocommerce-password-strength, .woocommerce-password-hint').remove();
            }
            var $loginForm = $('form.woocommerce-form-login.login');
            if ($loginForm.length) {
                $loginForm.data('hz-submitting', false);
                var $loginBtn = $loginForm.find('button[name="login"], .woocommerce-form-login__submit');
                $loginBtn.removeClass('hz-loading').prop('disabled', false).css('pointer-events', '');
                $loginBtn.text('Log in');
            }
            hideRegNotice();
            hideLoginNotice();
        }

        // Prevent form re-submission / stale state on reload for My Account
        if ($('.hz-account-auth-container').length && window.history && window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        // =========================================================================
        // Stage 1: Registration Email OTP Flow & 5-Minute Expiry Countdown
        // =========================================================================
        var otpTimerInterval = null;

        function showRegNotice(message, type) {
            var $notice = $('#hz-reg-notice');
            if (!$notice.length) return;

            if (!message || !$.trim(String(message).replace(/<[^>]+>/g, ''))) {
                hideRegNotice();
                return;
            }

            type = (type === 'success' || type === 'info') ? type : 'error';
            $notice.removeClass('is-error is-success is-info is-hidden')
                   .addClass('is-' + type)
                   .html(message)
                   .stop(true, true)
                   .show();
        }

        function hideRegNotice() {
            var $notice = $('#hz-reg-notice');
            if (!$notice.length) return;
            $notice.removeClass('is-error is-success is-info')
                   .addClass('is-hidden')
                   .empty()
                   .stop(true, true)
                   .hide();
        }

        function showLoginNotice(message, type) {
            var $notice = $('#hz-login-notice');
            if (!$notice.length) return;

            if (!message || !$.trim(String(message).replace(/<[^>]+>/g, ''))) {
                hideLoginNotice();
                return;
            }

            type = (type === 'success' || type === 'info') ? type : 'error';
            $notice.removeClass('is-error is-success is-info is-hidden')
                   .addClass('is-' + type)
                   .html(message)
                   .stop(true, true)
                   .show();
        }

        function hideLoginNotice() {
            var $notice = $('#hz-login-notice');
            if (!$notice.length) return;
            $notice.removeClass('is-error is-success is-info')
                   .addClass('is-hidden')
                   .empty()
                   .stop(true, true)
                   .hide();
        }

        var otpTimerInterval = null;
        var resendAvailabilityInterval = null;
        var resendCooldownInterval = null;
        var emailCooldownInterval = null;
        var emailChangeCooldownInterval = null;
        var currentEmailChangeCooldownUntil = 0;
        var activeCooledEmail = '';
        var serverTimeOffset = 0;
        var currentResendLimitReached = false;
        var currentCooldownUntil = 0;
        var currentResendAvailableAt = 0;

        function getNowSec() {
            return Math.floor(Date.now() / 1000) - serverTimeOffset;
        }

        function formatTime(sec) {
            var total = Math.max(0, parseInt(sec, 10) || 0);
            var mins = Math.floor(total / 60);
            var secs = total % 60;
            return (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;
        }

        function isEmailChangeCooled() {
            return (currentEmailChangeCooldownUntil > 0 && getNowSec() < currentEmailChangeCooldownUntil);
        }

        function clearEmailChangeCooldown() {
            if (emailChangeCooldownInterval) {
                clearInterval(emailChangeCooldownInterval);
                emailChangeCooldownInterval = null;
            }
            currentEmailChangeCooldownUntil = 0;
        }

        function startEmailChangeCooldownTicker(cdUntilTimestamp) {
            clearEmailChangeCooldown();

            currentEmailChangeCooldownUntil = parseInt(cdUntilTimestamp, 10) || 0;
            var $sendBtn = $('#hz-btn-send-otp');
            $sendBtn.prop('disabled', true).addClass('is-disabled');

            function tickEmailChangeCd() {
                var nowSec = getNowSec();
                var remaining = Math.max(0, currentEmailChangeCooldownUntil - nowSec);

                if (remaining > 0) {
                    $sendBtn.prop('disabled', true).addClass('is-disabled');
                    var formatted = formatTime(remaining);
                    showRegNotice("You've reached the email change limit. Please try again in " + formatted + ".", 'error');
                } else {
                    clearEmailChangeCooldown();
                    $sendBtn.prop('disabled', false).removeClass('is-disabled');
                    hideRegNotice();
                }
            }

            tickEmailChangeCd();
            if (currentEmailChangeCooldownUntil > getNowSec()) {
                emailChangeCooldownInterval = setInterval(tickEmailChangeCd, 1000);
            }
        }

        function setResendButtonText(text) {
            var $btn = $('#hz-btn-resend-otp');
            var $span = $btn.find('.hz-resend-btn-text');
            if ($span.length) {
                $span.text(text);
            } else {
                $btn.text(text);
            }
        }

        function clearEmailCooldown() {
            if (emailCooldownInterval) {
                clearInterval(emailCooldownInterval);
                emailCooldownInterval = null;
            }
            activeCooledEmail = '';
        }

        function clearAllTimers(keepEmailChangeCd) {
            if (otpTimerInterval) {
                clearInterval(otpTimerInterval);
                otpTimerInterval = null;
            }
            if (resendAvailabilityInterval) {
                clearInterval(resendAvailabilityInterval);
                resendAvailabilityInterval = null;
            }
            if (resendCooldownInterval) {
                clearInterval(resendCooldownInterval);
                resendCooldownInterval = null;
            }
            if (emailCooldownInterval) {
                clearInterval(emailCooldownInterval);
                emailCooldownInterval = null;
            }
            if (!keepEmailChangeCd) {
                clearEmailChangeCooldown();
            }
            activeCooledEmail = '';
        }

        function checkServerCooldown(email, callback) {
            email = (email || '').trim().toLowerCase();
            if (!email) {
                if (typeof callback === 'function') callback({ is_cooldown: false });
                return;
            }

            var ajaxUrl = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.ajax_url) ? window.handzom_checkout_ajax.ajax_url : '/wp-admin/admin-ajax.php';
            var nonce = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.nonce) ? window.handzom_checkout_ajax.nonce : '';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'handzom_check_otp_cooldown',
                    email: email,
                    nonce: nonce
                },
                success: function (res) {
                    if (res && res.success && res.data) {
                        if (res.data.server_time) {
                            serverTimeOffset = Math.floor(Date.now() / 1000) - parseInt(res.data.server_time, 10);
                        }
                        if (typeof callback === 'function') {
                            callback(res.data);
                        }
                    } else {
                        if (typeof callback === 'function') {
                            callback({ is_cooldown: false });
                        }
                    }
                },
                error: function () {
                    if (typeof callback === 'function') {
                        callback({ is_cooldown: false });
                    }
                }
            });
        }

        function startEmailCooldownTicker(email, cdUntilTimestamp) {
            clearEmailCooldown();

            email = (email || '').trim().toLowerCase();
            activeCooledEmail = email;
            var cdUntil = parseInt(cdUntilTimestamp, 10) || 0;

            function tickEmailCd() {
                var currentInput = ($('#reg_email').val() || '').trim().toLowerCase();
                if (currentInput !== activeCooledEmail) {
                    clearEmailCooldown();
                    return;
                }

                var nowSec = getNowSec();
                var remaining = Math.max(0, cdUntil - nowSec);

                if (remaining > 0) {
                    $('#hz-btn-send-otp').prop('disabled', true).addClass('is-disabled');
                    var formatted = formatTime(remaining);
                    showRegNotice("You've reached the OTP resend limit. Please try again in " + formatted + ".", 'error');
                } else {
                    clearEmailCooldown();
                    // When reaching 0, recheck server before re-enabling Send OTP
                    checkServerCooldown(email, function (serverState) {
                        var curEmail = ($('#reg_email').val() || '').trim().toLowerCase();
                        if (curEmail !== email) return;

                        if (serverState && serverState.is_cooldown && serverState.cooldown_remaining > 0) {
                            startEmailCooldownTicker(email, serverState.cooldown_until);
                        } else {
                            $('#hz-btn-send-otp').prop('disabled', false).removeClass('is-disabled');
                            hideRegNotice();
                        }
                    });
                }
            }

            tickEmailCd();
            if (cdUntil > getNowSec()) {
                emailCooldownInterval = setInterval(tickEmailCd, 1000);
            }
        }

        function startCooldownTicker(cdUntilTimestamp, email) {
            if (resendCooldownInterval) {
                clearInterval(resendCooldownInterval);
                resendCooldownInterval = null;
            }

            currentCooldownUntil = parseInt(cdUntilTimestamp, 10) || 0;
            currentResendLimitReached = true;
            email = (email || $('#reg_email').val() || $('#hz-otp-target-email').text() || '').trim().toLowerCase();

            function tickCd() {
                var nowSec = getNowSec();
                var remaining = Math.max(0, currentCooldownUntil - nowSec);

                if (remaining > 0) {
                    $('#hz-btn-resend-otp').prop('disabled', true).addClass('is-disabled');
                    setResendButtonText('Resend OTP');
                    var formatted = formatTime(remaining);
                    showRegNotice("You've reached the OTP resend limit. Please try again in " + formatted + ".", 'error');
                } else {
                    clearInterval(resendCooldownInterval);
                    resendCooldownInterval = null;

                    // When countdown reaches 0, recheck server before enabling Resend OTP
                    checkServerCooldown(email, function (serverState) {
                        if (serverState && serverState.is_cooldown && serverState.cooldown_remaining > 0) {
                            startCooldownTicker(serverState.cooldown_until, email);
                        } else {
                            currentResendLimitReached = false;
                            currentCooldownUntil = 0;
                            $('#hz-btn-resend-otp').prop('disabled', false).removeClass('is-disabled');
                            setResendButtonText('Resend OTP');
                            hideRegNotice();
                        }
                    });
                }
            }

            tickCd();
            if (currentCooldownUntil > getNowSec()) {
                resendCooldownInterval = setInterval(tickCd, 1000);
            }
        }

        function startResendAvailabilityTicker(resendAvailableAtTimestamp, resendLimitReached, cooldownUntilTimestamp, email) {
            if (resendAvailabilityInterval) {
                clearInterval(resendAvailabilityInterval);
                resendAvailabilityInterval = null;
            }

            var $resendBtn = $('#hz-btn-resend-otp');
            currentResendAvailableAt = parseInt(resendAvailableAtTimestamp, 10) || 0;
            currentResendLimitReached = !!resendLimitReached;
            email = (email || $('#reg_email').val() || $('#hz-otp-target-email').text() || '').trim().toLowerCase();

            var nowSec = getNowSec();
            if (currentResendLimitReached || (cooldownUntilTimestamp && cooldownUntilTimestamp > nowSec)) {
                $resendBtn.prop('disabled', true).addClass('is-disabled');
                setResendButtonText('Resend OTP');
                startCooldownTicker(cooldownUntilTimestamp, email);
                return;
            }

            function tickResend() {
                var now = getNowSec();
                var remaining = Math.max(0, currentResendAvailableAt - now);

                if (remaining > 0) {
                    $resendBtn.prop('disabled', true).addClass('is-disabled');
                    var formatted = formatTime(remaining);
                    setResendButtonText('Resend available in ' + formatted);
                } else {
                    clearInterval(resendAvailabilityInterval);
                    resendAvailabilityInterval = null;
                    if (!currentResendLimitReached) {
                        $resendBtn.prop('disabled', false).removeClass('is-disabled');
                        setResendButtonText('Resend OTP');
                    }
                }
            }

            tickResend();
            if (currentResendAvailableAt > getNowSec()) {
                resendAvailabilityInterval = setInterval(tickResend, 1000);
            }
        }

        function setOtpExpiredState(email) {
            if (otpTimerInterval) {
                clearInterval(otpTimerInterval);
                otpTimerInterval = null;
            }
            $('.hz-otp-timer-text').html('Expires in: <strong class="hz-otp-countdown is-expired" id="hz-otp-countdown">00:00</strong>');
            $('#reg_otp').prop('disabled', true);
            $('#hz-btn-verify-otp').prop('disabled', true).addClass('is-disabled');

            email = (email || $('#reg_email').val() || $('#hz-otp-target-email').text() || '').trim().toLowerCase();
            var nowSec = getNowSec();
            if (currentResendLimitReached || (currentCooldownUntil && currentCooldownUntil > nowSec)) {
                $('#hz-btn-resend-otp').prop('disabled', true).addClass('is-disabled');
                setResendButtonText('Resend OTP');
                startCooldownTicker(currentCooldownUntil, email);
            } else {
                if (!currentResendAvailableAt || nowSec >= currentResendAvailableAt) {
                    $('#hz-btn-resend-otp').prop('disabled', false).removeClass('is-disabled');
                    setResendButtonText('Resend OTP');
                }
                showRegNotice('Your verification code has expired. Please request a new OTP.', 'error');
            }
        }

        function startOtpCountdown(expiresAtTimestamp, resendAvailableAtTimestamp, cooldownUntilTimestamp, serverTimestamp, resendLimitReached, email) {
            if (otpTimerInterval) {
                clearInterval(otpTimerInterval);
                otpTimerInterval = null;
            }

            expiresAtTimestamp = parseInt(expiresAtTimestamp, 10) || 0;
            resendAvailableAtTimestamp = parseInt(resendAvailableAtTimestamp, 10) || 0;
            currentCooldownUntil = parseInt(cooldownUntilTimestamp, 10) || 0;
            currentResendLimitReached = !!resendLimitReached;
            email = (email || $('#reg_email').val() || $('#hz-otp-target-email').text() || '').trim().toLowerCase();

            if (serverTimestamp) {
                serverTimeOffset = Math.floor(Date.now() / 1000) - parseInt(serverTimestamp, 10);
            }

            function tickExpiry() {
                var nowSec = getNowSec();
                var remaining = Math.max(0, expiresAtTimestamp - nowSec);
                var formatted = formatTime(remaining);

                if (remaining > 0) {
                    $('.hz-otp-timer-text').html('Expires in: <strong class="hz-otp-countdown" id="hz-otp-countdown">' + formatted + '</strong>');
                    $('#reg_otp').prop('disabled', false);
                    $('#hz-btn-verify-otp').prop('disabled', false).removeClass('is-disabled');
                } else {
                    setOtpExpiredState(email);
                }
            }

            tickExpiry();
            if (expiresAtTimestamp > getNowSec()) {
                otpTimerInterval = setInterval(tickExpiry, 1000);
            }

            // Start 1-minute resend availability ticker
            startResendAvailabilityTicker(resendAvailableAtTimestamp, currentResendLimitReached, currentCooldownUntil, email);
        }

        function handleSendOtp($button) {
            if (isEmailChangeCooled()) {
                var remaining = Math.max(0, currentEmailChangeCooldownUntil - getNowSec());
                var formatted = formatTime(remaining);
                showRegNotice("You've reached the email change limit. Please try again in " + formatted + ".", 'error');
                $button.prop('disabled', true).addClass('is-disabled');
                return;
            }

            var email = ($('#reg_email').val() || $('#hz-otp-target-email').text() || '').trim();
            if (!email) {
                showRegNotice('Please enter your email address.', 'error');
                $('#reg_email').focus();
                return;
            }

            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showRegNotice('Please enter a valid email address.', 'error');
                $('#reg_email').focus();
                return;
            }

            var normalizedEmail = email.toLowerCase();
            var origText = $button.text().trim() || 'SEND OTP TO EMAIL';
            var isSendBtn = $button.is('#hz-btn-send-otp');
            var isResendBtn = $button.is('#hz-btn-resend-otp');

            $button.addClass('hz-loading').prop('disabled', true);
            if (isSendBtn) {
                $button.html('<span class="hz-btn-spinner" aria-hidden="true"></span><span class="hz-btn-text">SENDING CODE...</span>');
            } else if (isResendBtn) {
                setResendButtonText('Sending...');
            }

            var ajaxUrl = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.ajax_url) ? window.handzom_checkout_ajax.ajax_url : '/wp-admin/admin-ajax.php';
            var nonce = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.nonce) ? window.handzom_checkout_ajax.nonce : '';

            // Check server-side cooldown before sending another OTP
            checkServerCooldown(normalizedEmail, function (serverState) {
                if (serverState && serverState.email_change_cooldown && serverState.email_change_cooldown.is_active && serverState.email_change_cooldown.cooldown_remaining > 0) {
                    $button.removeClass('hz-loading').prop('disabled', true).addClass('is-disabled');
                    if (isSendBtn) {
                        $button.html('<span class="hz-btn-text">' + origText + '</span>');
                    } else if (isResendBtn) {
                        setResendButtonText('Resend OTP');
                    }
                    startEmailChangeCooldownTicker(serverState.email_change_cooldown.cooldown_until);
                    return;
                }

                if (serverState && serverState.is_cooldown && serverState.cooldown_remaining > 0) {
                    $button.removeClass('hz-loading').prop('disabled', true).addClass('is-disabled');
                    if (isSendBtn) {
                        $button.html('<span class="hz-btn-text">' + origText + '</span>');
                    } else if (isResendBtn) {
                        setResendButtonText('Resend OTP');
                    }

                    if ($('#hz-reg-step-otp').is(':visible')) {
                        startCooldownTicker(serverState.cooldown_until, normalizedEmail);
                    } else {
                        // Remain on initial email-entry step, display remaining cooldown message
                        $('#hz-reg-step-email').show();
                        $('#hz-reg-step-otp').removeClass('is-active').hide();
                        $('.hz-reg-stage2-fields').removeClass('is-active').hide();
                        startEmailCooldownTicker(normalizedEmail, serverState.cooldown_until);
                    }
                    return;
                }

                // If not in cooldown, clear stale notice and proceed with send OTP
                hideRegNotice();

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'handzom_send_registration_otp',
                        email: normalizedEmail,
                        nonce: nonce
                    },
                    success: function (res) {
                        $button.removeClass('hz-loading');
                        if (isSendBtn) {
                            $button.prop('disabled', false).html('<span class="hz-btn-text">' + origText + '</span>');
                        } else if (isResendBtn) {
                            setResendButtonText('Resend OTP');
                        }

                        if (res && res.success) {
                            $('#reg_email').val(normalizedEmail);
                            $('#hz-otp-target-email').text(normalizedEmail);
                            $('#hz-reg-step-email').slideUp(150);
                            $('#hz-reg-step-otp').addClass('is-active').hide().slideDown(200);
                            $('.hz-reg-stage2-fields').removeClass('is-active').hide();
                            $('#reg_otp').val('').prop('disabled', false).focus();
                            $('#hz-btn-verify-otp').prop('disabled', false).removeClass('is-disabled');
                            showRegNotice(res.data.message || 'Verification code sent to your email.', 'success');

                            if (res.data.expires_at) {
                                var cdUntil = res.data.resend_limit_reached ? (res.data.cooldown_until || 0) : 0;
                                startOtpCountdown(
                                    res.data.expires_at,
                                    res.data.resend_available_at || (res.data.server_time + 60),
                                    cdUntil,
                                    res.data.server_time,
                                    res.data.resend_limit_reached,
                                    normalizedEmail
                                );

                                if (res.data.resend_limit_reached && cdUntil > 0) {
                                    startCooldownTicker(cdUntil, normalizedEmail);
                                }
                            }
                        } else {
                            if (res && res.data && res.data.email_change_limit_reached) {
                                var cdUntil = res.data.cooldown_until || 0;
                                if (res.data.server_time) {
                                    serverTimeOffset = Math.floor(Date.now() / 1000) - parseInt(res.data.server_time, 10);
                                }
                                startEmailChangeCooldownTicker(cdUntil);
                                return;
                            }

                            var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Unable to send verification code. Please try again.';
                            showRegNotice(errMsg, 'error');

                            if (res && res.data && (res.data.resend_limit_reached || (res.data.cooldown_until && res.data.cooldown_until > getNowSec()))) {
                                currentResendLimitReached = true;
                                if ($('#hz-reg-step-otp').is(':visible')) {
                                    startCooldownTicker(res.data.cooldown_until, normalizedEmail);
                                } else {
                                    startEmailCooldownTicker(normalizedEmail, res.data.cooldown_until);
                                }
                            } else if (res && res.data && res.data.resend_available_at) {
                                startResendAvailabilityTicker(
                                    res.data.resend_available_at,
                                    false,
                                    0,
                                    normalizedEmail
                                );
                            }
                        }
                    },
                    error: function () {
                        $button.removeClass('hz-loading').prop('disabled', false);
                        if (isSendBtn) {
                            $button.html('<span class="hz-btn-text">' + origText + '</span>');
                        } else if (isResendBtn) {
                            setResendButtonText('Resend OTP');
                        }
                        showRegNotice('A network error occurred. Please check your connection and try again.', 'error');
                    }
                });
            });
        }

        function handleVerifyOtp($button) {
            if ($button.prop('disabled') || $button.hasClass('is-disabled')) {
                return;
            }

            if ($('#hz-otp-countdown').hasClass('is-expired')) {
                showRegNotice('Your verification code has expired. Please request a new OTP.', 'error');
                return;
            }

            var email = $('#reg_email').val().trim() || $('#hz-otp-target-email').text().trim();
            var otp = $('#reg_otp').val().trim();

            if (!email) {
                showRegNotice('Email address missing. Please request a new verification code.', 'error');
                return;
            }

            if (!otp || otp.length !== 6) {
                showRegNotice('Please enter the full 6-digit verification code.', 'error');
                $('#reg_otp').focus();
                return;
            }

            hideRegNotice();

            var origText = $button.text().trim() || 'VERIFY OTP';
            $button.addClass('hz-loading').prop('disabled', true);
            $button.html('<span class="hz-btn-spinner" aria-hidden="true"></span><span class="hz-btn-text">VERIFYING...</span>');

            var ajaxUrl = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.ajax_url) ? window.handzom_checkout_ajax.ajax_url : '/wp-admin/admin-ajax.php';
            var nonce = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.nonce) ? window.handzom_checkout_ajax.nonce : '';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'handzom_verify_registration_otp',
                    email: email,
                    otp: otp,
                    nonce: nonce
                },
                success: function (res) {
                    $button.removeClass('hz-loading').prop('disabled', false).text(origText);
                    if (res && res.success) {
                        clearAllTimers();

                        // Store verification token
                        $('#hz_reg_verification_token').val((res.data && res.data.token) ? res.data.token : '');

                        // Hide OTP step
                        $('#hz-reg-step-otp').removeClass('is-active').slideUp(150);

                        // Show success notice
                        showRegNotice(res.data.message || 'Email verified successfully!', 'success');

                        // Reveal Stage 2 Password fields & Create Account button
                        $('.hz-reg-stage2-fields').addClass('is-active').hide().slideDown(200);
                        $('#reg_password').focus();
                    } else {
                        var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Invalid verification code. Please check and try again.';
                        showRegNotice(errMsg, 'error');

                        if (res && res.data && res.data.expired) {
                            if (res.data.resend_limit_reached) {
                                currentResendLimitReached = true;
                                currentCooldownUntil = res.data.cooldown_until || 0;
                            }
                            setOtpExpiredState(email);
                        }
                    }
                },
                error: function () {
                    $button.removeClass('hz-loading').prop('disabled', false).text(origText);
                    showRegNotice('A network error occurred. Please check your connection and try again.', 'error');
                }
            });
        }

        $(document).on('click', '#hz-btn-send-otp', function (e) {
            e.preventDefault();
            if (isEmailChangeCooled()) {
                return false;
            }
            if ($(this).prop('disabled') || $(this).hasClass('is-disabled')) return;
            handleSendOtp($(this));
        });

        $(document).on('click', '#hz-btn-resend-otp', function (e) {
            e.preventDefault();
            if (isEmailChangeCooled()) {
                return false;
            }
            if ($(this).prop('disabled') || $(this).hasClass('is-disabled')) return;
            handleSendOtp($(this));
        });

        $(document).on('click', '#hz-btn-verify-otp', function (e) {
            e.preventDefault();
            if ($(this).prop('disabled') || $(this).hasClass('is-disabled')) return;
            handleVerifyOtp($(this));
        });

        $(document).on('click', '#hz-otp-change-email-btn', function (e) {
            e.preventDefault();
            var $btn = $(this);
            if ($btn.prop('disabled') || $btn.hasClass('is-disabled')) {
                return;
            }
            $btn.prop('disabled', true).addClass('is-disabled');

            var ajaxUrl = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.ajax_url) ? window.handzom_checkout_ajax.ajax_url : '/wp-admin/admin-ajax.php';
            var nonce = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.nonce) ? window.handzom_checkout_ajax.nonce : '';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'handzom_clear_registration_session',
                    nonce: nonce
                },
                success: function (res) {
                    $btn.prop('disabled', false).removeClass('is-disabled');
                    if (res && res.success) {
                        clearAllTimers();
                        hideRegNotice();

                        $('#hz-reg-step-otp').removeClass('is-active').slideUp(150);
                        $('.hz-reg-stage2-fields').removeClass('is-active').hide();
                        $('#hz-reg-step-email').slideDown(200);
                        $('#reg_otp').val('');

                        var $sendBtn = $('#hz-btn-send-otp');
                        $sendBtn.prop('disabled', false).removeClass('is-disabled hz-loading');
                        if ($sendBtn.find('.hz-btn-text').length) {
                            $sendBtn.find('.hz-btn-text').text('SEND OTP TO EMAIL');
                        } else {
                            $sendBtn.text('SEND OTP TO EMAIL');
                        }

                        $('#reg_email').focus();
                    } else {
                        if (res && res.data && res.data.email_change_limit_reached) {
                            // 4th change attempt -> block and apply 5-minute cooldown!
                            // Keep user on the email-entry step while cooldown is active
                            clearAllTimers(true);
                            $('#hz-reg-step-otp').removeClass('is-active').slideUp(150);
                            $('.hz-reg-stage2-fields').removeClass('is-active').hide();
                            $('#hz-reg-step-email').slideDown(200);
                            $('#reg_otp').val('');

                            var cdUntil = res.data.cooldown_until || 0;
                            if (res.data.server_time) {
                                serverTimeOffset = Math.floor(Date.now() / 1000) - parseInt(res.data.server_time, 10);
                            }
                            startEmailChangeCooldownTicker(cdUntil);
                        } else {
                            var errMsg = (res && res.data && res.data.message) ? res.data.message : 'Unable to change email. Please try again.';
                            showRegNotice(errMsg, 'error');
                        }
                    }
                },
                error: function () {
                    $btn.prop('disabled', false).removeClass('is-disabled');
                    showRegNotice('A connection error occurred. Please try again.', 'error');
                }
            });
        });

        // Pressing enter in email input triggers Send OTP
        $(document).on('keydown', '#reg_email', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                if (isEmailChangeCooled()) {
                    return false;
                }
                $('#hz-btn-send-otp').trigger('click');
            }
        });

        // Pressing enter in OTP input triggers Verify OTP
        $(document).on('keydown', '#reg_otp', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#hz-btn-verify-otp').trigger('click');
            }
        });

        // Numeric filtering for OTP input
        $(document).on('input', '#reg_otp', function () {
            var val = $(this).val().replace(/[^0-9]/g, '').substring(0, 6);
            $(this).val(val);
        });

        // Submit handler for registration form (AJAX, Prevent double submission & idempotent)
        $(document).on('submit', '#hz-register-form, form.woocommerce-form-register', function (e) {
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[name="register"], .woocommerce-form-register__submit');

            if ($form.data('hz-submitting')) {
                return false;
            }

            var email = $form.find('#reg_email').val() ? $form.find('#reg_email').val().trim() : '';
            var pass1 = $form.find('#reg_password').val();
            var pass2 = $form.find('#reg_password_confirmation').val();
            var token = $form.find('#hz_reg_verification_token').val();
            var username = $form.find('#reg_username').length ? $form.find('#reg_username').val() : '';
            var nonce = $form.find('#woocommerce-register-nonce').val() || (window.handzom_checkout_ajax ? window.handzom_checkout_ajax.nonce : '');

            if (!email) {
                showRegNotice('Please enter your email address.', 'error');
                $form.find('#reg_email').focus();
                return false;
            }

            if (!token) {
                showRegNotice('Please verify your email address with the verification code before creating an account.', 'error');
                return false;
            }

            if (!pass1) {
                showRegNotice('Please enter an account password.', 'error');
                $form.find('#reg_password').focus();
                return false;
            }

            if (!pass2) {
                showRegNotice('Please confirm your password.', 'error');
                $form.find('#reg_password_confirmation').focus();
                return false;
            }

            if (pass1 !== pass2) {
                showRegNotice('Passwords do not match. Please confirm your password.', 'error');
                $form.find('#reg_password_confirmation').focus();
                return false;
            }

            hideRegNotice();

            // Mark form as submitting and show loading indicator
            $form.data('hz-submitting', true);
            var origBtnHtml = $btn.html();
            $btn.addClass('hz-loading').prop('disabled', true);
            $btn.html('<span class="hz-btn-spinner" aria-hidden="true"></span><span class="hz-btn-text">CREATING ACCOUNT...</span>');

            var ajaxUrl = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.ajax_url) ? window.handzom_checkout_ajax.ajax_url : '/wp-admin/admin-ajax.php';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'handzom_register_account',
                    nonce: (window.handzom_checkout_ajax && window.handzom_checkout_ajax.nonce) ? window.handzom_checkout_ajax.nonce : '',
                    'woocommerce-register-nonce': nonce,
                    email: email,
                    username: username,
                    password: pass1,
                    password_confirmation: pass2,
                    hz_reg_verification_token: token
                },
                success: function (res) {
                    if (res && res.success) {
                        if (window.handzom_checkout_ajax) {
                            window.handzom_checkout_ajax.verified_session = null;
                            window.handzom_checkout_ajax.active_otp = null;
                            window.handzom_checkout_ajax.email_change_cooldown = null;
                        }
                        clearEmailChangeCooldown();
                        showRegNotice(res.data.message || 'Your Handzom account has been created successfully!', 'success');
                        $btn.removeClass('hz-loading').html('<span class="hz-btn-text">ACCOUNT CREATED</span>');
                        var redirectUrl = (res.data && res.data.redirect_url) ? res.data.redirect_url : '/';
                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 900);
                    } else {
                        $form.data('hz-submitting', false);
                        $btn.removeClass('hz-loading').prop('disabled', false).html(origBtnHtml);
                        var errorMsg = (res && res.data && res.data.message) ? res.data.message : 'Registration failed. Please check your details and try again.';
                        showRegNotice(errorMsg, 'error');
                    }
                },
                error: function () {
                    $form.data('hz-submitting', false);
                    $btn.removeClass('hz-loading').prop('disabled', false).html(origBtnHtml);
                    showRegNotice('A connection error occurred. Please try again.', 'error');
                }
            });

            return false;
        });

        // Submit handler for Login form (AJAX, In-card Notice & Homepage Redirect)
        $(document).on('submit', 'form.woocommerce-form-login.login', function (e) {
            var $form = $(this);
            if (!$form.closest('.hz-account-auth-container').length) {
                return;
            }

            e.preventDefault();
            var $btn = $form.find('button[name="login"], .woocommerce-form-login__submit');

            if ($form.data('hz-submitting')) {
                return false;
            }

            var username = $form.find('#username').val() ? $form.find('#username').val().trim() : '';
            var password = $form.find('#password').val();
            var remember = $form.find('#rememberme').is(':checked') ? 'forever' : '';
            var nonce = $form.find('#woocommerce-login-nonce').val() || (window.handzom_checkout_ajax ? window.handzom_checkout_ajax.nonce : '');

            if (!username) {
                showLoginNotice('Please enter your username or email address.', 'error');
                $form.find('#username').focus();
                return false;
            }

            if (!password) {
                showLoginNotice('Please enter your password.', 'error');
                $form.find('#password').focus();
                return false;
            }

            hideLoginNotice();

            $form.data('hz-submitting', true);
            var origBtnHtml = $btn.html();
            $btn.addClass('hz-loading').prop('disabled', true);
            $btn.html('<span class="hz-btn-spinner" aria-hidden="true"></span><span class="hz-btn-text">LOGGING IN...</span>');

            var ajaxUrl = (window.handzom_checkout_ajax && window.handzom_checkout_ajax.ajax_url) ? window.handzom_checkout_ajax.ajax_url : '/wp-admin/admin-ajax.php';

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'handzom_login_account',
                    nonce: (window.handzom_checkout_ajax && window.handzom_checkout_ajax.nonce) ? window.handzom_checkout_ajax.nonce : '',
                    'woocommerce-login-nonce': nonce,
                    username: username,
                    password: password,
                    rememberme: remember
                },
                success: function (res) {
                    if (res && res.success) {
                        showLoginNotice(res.data.message || 'Login successful! Redirecting...', 'success');
                        $btn.removeClass('hz-loading').html('<span class="hz-btn-text">LOGGED IN</span>');
                        var redirectUrl = (res.data && res.data.redirect_url) ? res.data.redirect_url : '/';
                        setTimeout(function () {
                            window.location.href = redirectUrl;
                        }, 900);
                    } else {
                        $form.data('hz-submitting', false);
                        $btn.removeClass('hz-loading').prop('disabled', false).html(origBtnHtml);
                        var errorMsg = (res && res.data && res.data.message) ? res.data.message : 'Invalid username or password.';
                        showLoginNotice(errorMsg, 'error');
                    }
                },
                error: function () {
                    $form.data('hz-submitting', false);
                    $btn.removeClass('hz-loading').prop('disabled', false).html(origBtnHtml);
                    showLoginNotice('A connection error occurred. Please try again.', 'error');
                }
            });

            return false;
        });

        // Initialize registration card state strictly from valid server-side state
        function initRegistrationFormState() {
            var ajaxState = window.handzom_checkout_ajax || {};
            var verifiedSession = ajaxState.verified_session;
            var activeOtp = ajaxState.active_otp;
            var cdInfo = ajaxState.cooldown_info;
            var emailChangeCd = ajaxState.email_change_cooldown;

            clearAllTimers();

            // Default clean state: Step 1 strictly visible, Step 2 & Stage 2 strictly hidden
            $('#hz-reg-step-email').show();
            $('#hz-reg-step-otp').removeClass('is-active').hide();
            $('.hz-reg-stage2-fields').removeClass('is-active').hide();

            if (emailChangeCd && emailChangeCd.is_active && emailChangeCd.cooldown_remaining > 0) {
                // Email change cooldown active! Keep user strictly on Step 1 (email entry step)
                $('#hz-reg-step-email').show();
                $('#hz-reg-step-otp').removeClass('is-active').hide();
                $('.hz-reg-stage2-fields').removeClass('is-active').hide();
                $('#hz-btn-send-otp').prop('disabled', true).addClass('is-disabled');

                if (emailChangeCd.server_time) {
                    serverTimeOffset = Math.floor(Date.now() / 1000) - parseInt(emailChangeCd.server_time, 10);
                }

                startEmailChangeCooldownTicker(emailChangeCd.cooldown_until);
            } else if (verifiedSession && verifiedSession.email && verifiedSession.token) {
                // Only show Stage 2 (Password creation) when server confirms verified session
                $('#reg_email').val(verifiedSession.email);
                $('#hz_reg_verification_token').val(verifiedSession.token);
                $('#hz-reg-step-email').hide();
                $('#hz-reg-step-otp').removeClass('is-active').hide();
                showRegNotice('Email verified successfully!', 'success');
                $('.hz-reg-stage2-fields').addClass('is-active').show();
            } else if (cdInfo && cdInfo.email && cdInfo.cooldown_remaining > 0) {
                // Server confirms 10-Minute Cooldown active for this email!
                // Stay strictly on Step 1 (email entry step)
                $('#reg_email').val(cdInfo.email);
                $('#hz-reg-step-email').show();
                $('#hz-reg-step-otp').removeClass('is-active').hide();
                $('.hz-reg-stage2-fields').removeClass('is-active').hide();

                if (cdInfo.server_time) {
                    serverTimeOffset = Math.floor(Date.now() / 1000) - parseInt(cdInfo.server_time, 10);
                }

                startEmailCooldownTicker(cdInfo.email, cdInfo.cooldown_until);
            } else if (activeOtp && activeOtp.email) {
                // Pending OTP stage
                $('#reg_email').val(activeOtp.email);
                $('#hz-otp-target-email').text(activeOtp.email);
                $('#hz-reg-step-email').hide();
                $('#hz-reg-step-otp').addClass('is-active').show();
                $('.hz-reg-stage2-fields').removeClass('is-active').hide();

                if (activeOtp.is_expired) {
                    currentResendLimitReached = !!activeOtp.resend_limit_reached;
                    currentCooldownUntil = activeOtp.cooldown_until || 0;
                    setOtpExpiredState(activeOtp.email);
                } else {
                    startOtpCountdown(
                        activeOtp.expires_at,
                        activeOtp.resend_available_at || (activeOtp.server_time + 60),
                        activeOtp.cooldown_until,
                        activeOtp.server_time,
                        activeOtp.resend_limit_reached,
                        activeOtp.email
                    );
                }
            } else {
                // Fresh visit / no valid pending state -> Step 1 only
                $('#hz-reg-step-email').show();
                $('#hz-reg-step-otp').removeClass('is-active').hide();
                $('.hz-reg-stage2-fields').removeClass('is-active').hide();
                $('#hz-btn-send-otp').prop('disabled', false).removeClass('is-disabled');
                hideRegNotice();
            }
        }

        // Clear cooldown ticker and notice when entering a different email
        $(document).on('input', '#reg_email', function () {
            if (isEmailChangeCooled()) {
                return;
            }
            var inputEmail = $(this).val().trim().toLowerCase();
            if (activeCooledEmail && inputEmail !== activeCooledEmail) {
                clearEmailCooldown();
                $('#hz-btn-send-otp').prop('disabled', false).removeClass('is-disabled');
                hideRegNotice();
            }
        });

        // When user enters/blurs with an email, check if it's cooled-down on server
        $(document).on('blur', '#reg_email', function () {
            if (isEmailChangeCooled()) {
                return;
            }
            var inputEmail = $(this).val().trim().toLowerCase();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(inputEmail)) return;

            // Only check if we are on step 1
            if (!$('#hz-reg-step-email').is(':visible')) return;

            checkServerCooldown(inputEmail, function (serverState) {
                if (isEmailChangeCooled()) return;
                var curEmail = ($('#reg_email').val() || '').trim().toLowerCase();
                if (curEmail !== inputEmail) return;

                if (serverState && serverState.is_cooldown && serverState.cooldown_remaining > 0) {
                    startEmailCooldownTicker(inputEmail, serverState.cooldown_until);
                } else if (activeCooledEmail === inputEmail) {
                    clearEmailCooldown();
                    $('#hz-btn-send-otp').prop('disabled', false).removeClass('is-disabled');
                    hideRegNotice();
                }
            });
        });

        if (!isEmailChangeCooled() && !$('#hz-reg-notice').text().trim()) {
            hideRegNotice();
        }
        if (!$('#hz-login-notice').text().trim()) {
            hideLoginNotice();
        }

        initRegistrationFormState();

        $(window).on('pageshow', function (e) {
            resetPlaceOrderLoading();
            if (e.originalEvent && e.originalEvent.persisted) {
                // If restored from Back-Forward cache, clear stale notices and inputs
                $('.woocommerce-notices-wrapper .woocommerce-error, ul.woocommerce-error').remove();
                resetRegistrationFormFields();
                initRegistrationFormState();
            }
        });

        // Order Pay page Pay Now loading state
        $(document).on('click submit', '.hz-order-pay-page #btn-razorpay, .hz-order-pay-page #submit_razorpay_payment_form, .hz-order-pay-page #place_order, .hz-order-pay-page form#order_review, .hz-order-pay-page form#razorpay-payment-form', function (e) {
            var $btn = $(this).is('form') ? $(this).find('input[type="submit"], button[type="submit"], #btn-razorpay, #submit_razorpay_payment_form, #place_order') : $(this);
            if ($btn.length && !$btn.hasClass('hz-loading')) {
                $btn.addClass('hz-loading');
                if ($btn.is('input')) {
                    $btn.val('PROCESSING...');
                } else {
                    $btn.html('<span class="hz-btn-spinner" aria-hidden="true"></span><span class="hz-btn-text">PROCESSING...</span>');
                }
            }
        });

        resetPlaceOrderLoading();

        // =========================================================================
        // Force Select2 / selectWoo Dropdown to ALWAYS Open Below
        // =========================================================================
        function forceDropdownBelow(dropdownInstance) {
            if (!dropdownInstance || !dropdownInstance.$container || !dropdownInstance.$dropdown) {
                return;
            }

            var $container = dropdownInstance.$container;
            var $dropdown = dropdownInstance.$dropdown;
            var $openContainer = $dropdown.closest('.select2-container--open');
            if (!$openContainer.length) {
                $openContainer = $dropdown.parent();
            }

            // 1. Force classes to 'below'
            $dropdown.removeClass('select2-dropdown--above').addClass('select2-dropdown--below');
            $container.removeClass('select2-container--above').addClass('select2-container--below');
            $openContainer.removeClass('select2-container--above').addClass('select2-container--below');

            // 2. Position below the container
            var offset = $container.offset();
            if (!offset) return;

            var containerHeight = $container.outerHeight(false);
            var containerWidth = $container.outerWidth(false);
            var targetTop = offset.top + containerHeight;

            $openContainer.css({
                top: targetTop + 'px',
                left: offset.left + 'px',
                width: containerWidth + 'px',
                position: 'absolute'
            });

            // 3. Smoothly scroll if dropdown is clipped at bottom of viewport
            var dropdownHeight = 240;
            var windowScrollTop = $(window).scrollTop();
            var windowHeight = $(window).height();
            var windowBottom = windowScrollTop + windowHeight;
            var dropdownBottom = targetTop + dropdownHeight;

            if (dropdownBottom > windowBottom) {
                var neededScroll = dropdownBottom - windowBottom + 30;
                $('html, body').stop().animate({
                    scrollTop: windowScrollTop + neededScroll
                }, 200);
            }
        }

        function patchSelect2Prototype(s2Instance) {
            if (!s2Instance || !s2Instance.dropdown) return;
            var dropdownProto = s2Instance.dropdown.constructor ? s2Instance.dropdown.constructor.prototype : Object.getPrototypeOf(s2Instance.dropdown);
            if (dropdownProto && !dropdownProto._hzPatched) {
                dropdownProto._hzPatched = true;
                dropdownProto._positionDropdown = function () {
                    forceDropdownBelow(this);
                };
                if (typeof dropdownProto.position === 'function') {
                    dropdownProto.position = function () {
                        forceDropdownBelow(this);
                    };
                }
            }
            // Also override directly on instance in case prototype is masked
            s2Instance.dropdown._positionDropdown = function () {
                forceDropdownBelow(this);
            };
            if (typeof s2Instance.dropdown.position === 'function') {
                s2Instance.dropdown.position = function () {
                    forceDropdownBelow(this);
                };
            }
        }

        // Hook on document for select2/selectWoo open
        $(document).on('select2:open selectWoo:open', function (e) {
            var $select = $(e.target);
            var s2 = $select.data('select2') || $select.data('selectWoo');
            if (s2) {
                patchSelect2Prototype(s2);
                if (s2.dropdown) {
                    forceDropdownBelow(s2.dropdown);
                }
                setTimeout(function () { if (s2.dropdown) forceDropdownBelow(s2.dropdown); }, 10);
                setTimeout(function () { if (s2.dropdown) forceDropdownBelow(s2.dropdown); }, 50);
                setTimeout(function () { if (s2.dropdown) forceDropdownBelow(s2.dropdown); }, 150);
            }
        });

        // Patch all existing and dynamically loaded country/state selects
        function patchAllSelects() {
            $('select.country_select, select.state_select, #billing_country, #shipping_country, #billing_state, #shipping_state').each(function () {
                var s2 = $(this).data('select2') || $(this).data('selectWoo');
                if (s2) {
                    patchSelect2Prototype(s2);
                }
            });
        }
        patchAllSelects();
        setTimeout(patchAllSelects, 300);
        setTimeout(patchAllSelects, 800);
        setTimeout(patchAllSelects, 2000);

        $(document.body).on('updated_checkout country_to_state_changed', function () {
            setTimeout(patchAllSelects, 100);
        });

        // Clean up any WooCommerce / theme default password toggle buttons
        function removeInjectedPasswordToggles() {
            $('.show-password-input, .wp-hide-pw, .password-input-toggle').each(function () {
                if (!$(this).hasClass('hz-password-toggle-btn') && !$(this).closest('.hz-password-toggle-btn').length) {
                    $(this).remove();
                }
            });
        }
        removeInjectedPasswordToggles();
        setTimeout(removeInjectedPasswordToggles, 100);
        setTimeout(removeInjectedPasswordToggles, 500);
        setTimeout(removeInjectedPasswordToggles, 1500);
        $(document).on('input keyup change focus', 'input[type="password"]', function () {
            removeInjectedPasswordToggles();
        });

        // Show / Hide Password Visibility Toggle
        $(document).on('click', '.hz-password-toggle-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            var $input = $btn.siblings('.hz-password-input');
            if (!$input.length) {
                $input = $btn.closest('.hz-input-password-wrap').find('input');
            }

            if ($input.attr('type') === 'password') {
                // Show password as plain text -> Show OPEN eye
                $input.attr('type', 'text');
                $btn.addClass('is-showing-password');
                $btn.attr('aria-label', 'Hide password').attr('aria-pressed', 'true');
            } else {
                // Hide password as dots -> Show CLOSED eye
                $input.attr('type', 'password');
                $btn.removeClass('is-showing-password');
                $btn.attr('aria-label', 'Show password').attr('aria-pressed', 'false');
            }
        });

        // Smooth focus to login username input when clicking log in link in notices
        $(document).on('click', '.hz-focus-login', function (e) {
            var $username = $('#username');
            if ($username.length) {
                e.preventDefault();
                $('html, body').animate({
                    scrollTop: $username.offset().top - 120
                }, 300);
                $username.focus();
            }
        });
    });

})(jQuery);
