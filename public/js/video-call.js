/**
 * Video Call Handler using Jitsi Meet
 * With JaaS (primary) + Self-hosted (fallback) support
 */

(function($) {
    'use strict';

    // ===========================================
    // JITSI SERVER CONFIGURATION
    // ===========================================
    // Primary: JaaS (Jitsi as a Service) - 25 free minutes/month
    // Fallback: Self-hosted Jitsi server
    // ===========================================

    const JITSI_SERVERS = {
        // PRIMARY: JaaS by 8x8 (get your AppID at https://jaas.8x8.vc/)
        jaas: {
            domain: '8x8.vc',
            appId: 'vpaas-magic-cookie-2cdc40053c22434a96f43d5ea189dea0', // TODO: Ganti dengan AppID dari akun JaaS kamu sendiri, untuk sementara pakai ini:             appId: 'vpaas-magic-cookie-2cdc40053c22434a96f43d5ea189dea0', // TODO: Ganti dengan AppID dari akun JaaS kamu sendiri, untuk sementara pakai ini: vpaas-magic-cookie-2cdc40053c22434a96f43d5ea189dea0/ca41d8
            enabled: true // DISABLED - karena AppID tidak valid. Set true setelah dapat AppID sendiri
        },
        // FALLBACK: Self-hosted Jitsi (install on your own VPS)
        selfHosted: {
            domain: 'YOUR_DOMAIN.com', // Replace with your Jitsi server domain e.g., 'jitsi.yourdomain.com'
            enabled: false // Set to true after you setup your server
        },
        // DEVELOPMENT: Public servers (for testing only - requires login)
        public: {
            domain: 'meet.jit.si',
            enabled: false // Aktif untuk sementara - butuh login Google/GitHub
        }
    };

    // Configuration - will be set from blade
    let config = {
        domain: '',
        roomName: '',
        userName: '',
        userEmail: '',
        csrfToken: '',
        saveRecordingUrl: '',
        saveScreenSharingUrl: '',
        sessionEndTime: null,
        useJaaS: false,
        jaasAppId: ''
    };

    // State
    let api = null;
    let screenSharingEvents = [];
    let sessionStartTime = new Date();
    let timerInterval = null;
    let connectionTimeout = null;
    let isConnected = false;
    let currentServer = null;
    let fallbackAttempted = false;

    /**
     * Determine which server to use
     */
    function selectServer() {
        // Priority: JaaS > Self-hosted > Public
        if (JITSI_SERVERS.jaas.enabled && JITSI_SERVERS.jaas.appId !== 'YOUR_JAAS_APP_ID') {
            return {
                type: 'jaas',
                domain: JITSI_SERVERS.jaas.domain,
                appId: JITSI_SERVERS.jaas.appId
            };
        }

        if (JITSI_SERVERS.selfHosted.enabled && JITSI_SERVERS.selfHosted.domain !== 'YOUR_DOMAIN.com') {
            return {
                type: 'selfHosted',
                domain: JITSI_SERVERS.selfHosted.domain,
                appId: null
            };
        }

        return {
            type: 'public',
            domain: JITSI_SERVERS.public.domain,
            appId: null
        };
    }

    /**
     * Get fallback server
     */
    function getFallbackServer() {
        if (currentServer.type === 'jaas' && JITSI_SERVERS.selfHosted.enabled) {
            return {
                type: 'selfHosted',
                domain: JITSI_SERVERS.selfHosted.domain,
                appId: null
            };
        }

        if (currentServer.type === 'jaas' || currentServer.type === 'selfHosted') {
            return {
                type: 'public',
                domain: JITSI_SERVERS.public.domain,
                appId: null
            };
        }

        return null;
    }

    /**
     * Build room name based on server type
     */
    function buildRoomName(server, baseRoomName) {
        if (server.type === 'jaas' && server.appId) {
            // JaaS requires: AppID/roomName
            return server.appId + '/' + baseRoomName;
        }
        return baseRoomName;
    }

    /**
     * Initialize the video call module
     */
    window.VideoCall = {
        init: function(options) {
            $.extend(config, options);

            // Select appropriate server
            currentServer = selectServer();
            config.domain = currentServer.domain;

            console.log('=== VideoCall Initialization ===');
            console.log('Server Type:', currentServer.type);
            console.log('Domain:', config.domain);
            console.log('Base Room Name:', config.roomName);
            console.log('User Name:', config.userName);

            if (currentServer.type === 'jaas') {
                console.log('JaaS AppID:', currentServer.appId);
                console.log('Full Room:', buildRoomName(currentServer, config.roomName));
            }

            console.log('Full Jitsi URL: https://' + config.domain + '/' + buildRoomName(currentServer, config.roomName));
            console.log('================================');

            // Show room name in UI for debugging
            showRoomInfo();

            // Validate required config
            if (!config.roomName) {
                showError('Room name is not configured. Please contact support.');
                return;
            }

            // Setup UI events
            setupEventListeners();

            // Initialize Jitsi
            initializeJitsi();
        }
    };

    /**
     * Show room info in UI for debugging
     */
    function showRoomInfo() {
        // Remove existing room info
        $('#roomInfo').remove();
        $('#serverInfo').remove();

        // Server type badge color
        var serverColor = currentServer.type === 'jaas' ? 'rgba(76, 201, 240, 0.2)' :
                         currentServer.type === 'selfHosted' ? 'rgba(76, 175, 80, 0.2)' :
                         'rgba(255, 193, 7, 0.2)';

        var serverLabel = currentServer.type === 'jaas' ? '☁️ JaaS' :
                         currentServer.type === 'selfHosted' ? '🖥️ Self-hosted' :
                         '🌐 Public';

        // Add server info
        var serverInfoHtml = '<div id="serverInfo" class="meta-item" style="background: ' + serverColor + '; padding: 5px 10px; border-radius: 5px;">' +
            '<span>' + serverLabel + ' <small>(' + config.domain + ')</small></span>' +
            '</div>';
        $('.session-meta').append(serverInfoHtml);

        // Add room info
        var roomInfoHtml = '<div id="roomInfo" class="meta-item" style="background: rgba(67, 97, 238, 0.2); padding: 5px 10px; border-radius: 5px;">' +
            '<i class="fas fa-link"></i>' +
            '<span>Room: <strong>' + config.roomName + '</strong></span>' +
            '</div>';
        $('.session-meta').append(roomInfoHtml);
    }

    /**
     * Setup jQuery event listeners
     */
    function setupEventListeners() {
        // End session button
        $('#endSession').on('click', function(e) {
            e.preventDefault();
            endSession();
        });

        // Save recording button
        $('#saveRecording').on('click', function(e) {
            e.preventDefault();
            saveRecording();
        });

        // Toggle side panel
        $('#togglePanel').on('click', function() {
            var $panel = $('#sidePanel');
            var $icon = $('#panelIcon');

            $panel.toggleClass('active');
            $icon.toggleClass('fa-chevron-left fa-chevron-right');
        });

        // Retry connection button
        $(document).on('click', '#retryButton', function() {
            retryConnection();
        });

        // Auto-save screen sharing data every 30 seconds
        setInterval(saveFinalScreenSharingData, 30000);

        // Handle page unload
        $(window).on('beforeunload', function() {
            saveFinalScreenSharingData();
        });

        // Auto-end session when time is up
        if (config.sessionEndTime) {
            var timeUntilEnd = new Date(config.sessionEndTime) - new Date();
            if (timeUntilEnd > 0) {
                setTimeout(function() {
                    if (confirm('Session time is up! The session will end automatically.')) {
                        endSession();
                    }
                }, timeUntilEnd);
            }
        }
    }

    /**
     * Initialize Jitsi Meet
     */
    function initializeJitsi() {
        try {
            updateLoadingStatus('Connecting to ' + currentServer.type + ' server...');

            // Clear any existing timeout
            if (connectionTimeout) {
                clearTimeout(connectionTimeout);
            }

            // Clear previous Jitsi instance if exists
            if (api) {
                api.dispose();
                api = null;
            }

            // Clear meet container
            $('#meet').empty();

            // Build the room name based on server type
            var fullRoomName = buildRoomName(currentServer, config.roomName);

            console.log('Connecting to:', currentServer.type);
            console.log('Domain:', config.domain);
            console.log('Full Room Name:', fullRoomName);

            // Jitsi Meet options
            var options = {
                roomName: fullRoomName,
                width: '100%',
                height: '100%',
                parentNode: document.querySelector('#meet'),
                userInfo: {
                    displayName: config.userName,
                    email: config.userEmail
                },
                configOverwrite: {
                    // Disable prejoin for JaaS and self-hosted
                    prejoinPageEnabled: currentServer.type === 'public',
                    prejoinConfig: {
                        enabled: currentServer.type === 'public'
                    },

                    // Start settings
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,

                    // Disable unnecessary features
                    enableWelcomePage: false,
                    enableClosePage: false,
                    disableDeepLinking: true,

                    // Connection
                    enableIceRestart: true,

                    // Language
                    defaultLanguage: 'en'
                },
                interfaceConfigOverwrite: {
                    DEFAULT_BACKGROUND: '#474747',
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    SHOW_BRAND_WATERMARK: false,
                    SHOW_POWERED_BY: false,
                    SHOW_PROMOTIONAL_CLOSE_PAGE: false,
                    MOBILE_APP_PROMO: false,
                    HIDE_INVITE_MORE_HEADER: true,

                    // Simplified toolbar
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'desktop', 'fullscreen',
                        'fodeviceselection', 'hangup', 'chat', 'settings',
                        'videoquality', 'filmstrip', 'tileview', 'raisehand'
                    ],

                    SETTINGS_SECTIONS: ['devices', 'language']
                }
            };

            // Create Jitsi instance
            api = new JitsiMeetExternalAPI(config.domain, options);

            // Hide loading immediately after iframe loads
            hideLoadingScreen();
            startSessionTimer();

            // Add event listeners
            api.addEventListener('videoConferenceJoined', handleConferenceJoined);
            api.addEventListener('videoConferenceLeft', handleConferenceLeft);
            api.addEventListener('participantJoined', handleParticipantJoined);
            api.addEventListener('participantLeft', handleParticipantLeft);
            api.addEventListener('readyToClose', handleReadyToClose);

            // Browser support check
            api.addEventListener('browserSupport', function(event) {
                console.log('Browser support:', event);
                if (!event.supported) {
                    showNotification('Your browser may not fully support video calls', 'warning');
                }
            });

            // Error handling - with fallback support
            api.addEventListener('errorOccurred', function(event) {
                console.error('Jitsi error:', event);

                // Check if this is a quota/limit error and try fallback
                var errorMessage = event.error || event.message || '';
                if (errorMessage.toLowerCase().includes('quota') ||
                    errorMessage.toLowerCase().includes('limit') ||
                    errorMessage.toLowerCase().includes('exceeded')) {

                    console.log('Quota/limit error detected, attempting fallback...');
                    tryFallbackServer();
                } else {
                    showNotification('Video call error: ' + (event.error || 'Unknown error'), 'error');
                }
            });

            // Set timeout to detect connection issues (try fallback after 30 seconds)
            connectionTimeout = setTimeout(function() {
                if (!isConnected) {
                    console.log('Connection timeout, trying fallback server...');
                    tryFallbackServer();
                }
            }, 30000);

            console.log('Jitsi API initialized - iframe should be visible now');

        } catch (error) {
            console.error('Error initializing Jitsi Meet:', error);
            tryFallbackServer();
        }
    }

    /**
     * Try fallback server if primary fails
     */
    function tryFallbackServer() {
        if (fallbackAttempted) {
            handleConnectionError('All servers failed. Please try again later.');
            return;
        }

        var fallback = getFallbackServer();
        if (fallback) {
            fallbackAttempted = true;
            console.log('=== SWITCHING TO FALLBACK SERVER ===');
            console.log('From:', currentServer.type, '(' + config.domain + ')');
            console.log('To:', fallback.type, '(' + fallback.domain + ')');
            console.log('====================================');

            showNotification('Switching to backup server...', 'warning');

            currentServer = fallback;
            config.domain = fallback.domain;

            // Update UI
            showRoomInfo();

            // Reinitialize with fallback server
            setTimeout(function() {
                initializeJitsi();
            }, 1000);
        } else {
            handleConnectionError('Failed to connect to video server. Please try again later.');
        }
    }

    /**
     * Handle conference joined
     */
    function handleConferenceJoined(event) {
        console.log('=== CONFERENCE JOINED ===');
        console.log('Event details:', event);
        console.log('Room name:', event.roomName);
        console.log('========================');

        isConnected = true;

        // Clear timeout if any
        if (connectionTimeout) {
            clearTimeout(connectionTimeout);
        }

        // Show success notification
        showNotification('Successfully joined the session! Waiting for other participants...', 'success');

        // Update UI with connection status
        updateParticipantCount();

        // Add screen sharing listener after join
        api.addEventListener('screenSharingStatusChanged', handleScreenSharing);
    }

    /**
     * Handle conference left
     */
    function handleConferenceLeft(event) {
        console.log('Left the conference', event);
        isConnected = false;
        stopSessionTimer();
        showNotification('You have left the session.', 'info');
    }

    /**
     * Handle participant joined
     */
    function handleParticipantJoined(event) {
        console.log('=== PARTICIPANT JOINED ===');
        console.log('Participant ID:', event.id);
        console.log('Display Name:', event.displayName);
        console.log('========================');

        var name = event.displayName || 'Someone';
        showNotification('🎉 ' + name + ' joined the session!', 'success');

        // Update participant count
        updateParticipantCount();
    }

    /**
     * Handle participant left
     */
    function handleParticipantLeft(event) {
        console.log('Participant left:', event);
        var name = event.displayName || 'Someone';
        showNotification(name + ' left the session', 'warning');

        // Update participant count
        updateParticipantCount();
    }

    /**
     * Update participant count in UI
     */
    function updateParticipantCount() {
        if (api) {
            var count = api.getNumberOfParticipants();
            console.log('Current participants in room:', count);

            // Update UI if element exists
            if ($('#participantCount').length === 0) {
                var countHtml = '<div id="participantCount" class="meta-item" style="background: rgba(76, 201, 240, 0.2); padding: 5px 10px; border-radius: 5px;">' +
                    '<i class="fas fa-users"></i>' +
                    '<span>Participants: <strong>' + count + '</strong></span>' +
                    '</div>';
                $('.session-meta').append(countHtml);
            } else {
                $('#participantCount strong').text(count);
            }
        }
    }

    /**
     * Handle ready to close
     */
    function handleReadyToClose() {
        console.log('Ready to close');
        endSession();
    }

    /**
     * Handle screen sharing status change
     */
    function handleScreenSharing(event) {
        if (event.on) {
            saveScreenSharingEvent('started', new Date());
            updateSharingActivity('Screen sharing started');
            showNotification('Screen sharing started', 'info');
        } else {
            saveScreenSharingEvent('stopped', new Date());
            updateSharingActivity('Screen sharing stopped');
            showNotification('Screen sharing stopped', 'info');
        }
    }

    /**
     * Handle connection error
     */
    function handleConnectionError(message) {
        console.error('Connection error:', message);

        // Clear timeout
        if (connectionTimeout) {
            clearTimeout(connectionTimeout);
        }

        // Show error in loading screen
        showError(message);
    }

    /**
     * Retry connection
     */
    function retryConnection() {
        // Reset state
        isConnected = false;

        // Dispose existing API
        if (api) {
            try {
                api.dispose();
            } catch (e) {
                console.error('Error disposing API:', e);
            }
            api = null;
        }

        // Show loading
        showLoading('Retrying connection...');

        // Reinitialize after delay
        setTimeout(function() {
            initializeJitsi();
        }, 1000);
    }

    /**
     * Show loading screen
     */
    function showLoading(message) {
        var $loading = $('#loadingScreen');
        $loading.find('.loading-text').text(message || 'Connecting to session...');
        $loading.find('.error-message').hide();
        $loading.find('.retry-btn').hide();
        $loading.find('.loading-spinner').show();
        $loading.show();
    }

    /**
     * Hide loading screen
     */
    function hideLoadingScreen() {
        $('#loadingScreen').fadeOut(300);
    }

    /**
     * Show error in loading screen
     */
    function showError(message) {
        var $loading = $('#loadingScreen');
        $loading.find('.loading-spinner').hide();
        $loading.find('.loading-text').text('Connection Error');
        $loading.find('.error-message').text(message).show();
        $loading.find('.retry-btn').show();
        $loading.show();
    }

    /**
     * Update loading status
     */
    function updateLoadingStatus(message) {
        $('#loadingScreen .loading-text').text(message);
        console.log('Status:', message);
    }

    /**
     * Show notification
     */
    function showNotification(message, type) {
        type = type || 'success';

        var $notification = $('#notification');
        var $text = $('#notificationText');
        var $icon = $notification.find('i');

        $text.text(message);
        $notification.removeClass('error warning info success');

        // Set icon based on type
        var iconClass = 'fas fa-check-circle';
        if (type === 'error') {
            iconClass = 'fas fa-exclamation-circle';
            $notification.addClass('error');
        } else if (type === 'warning') {
            iconClass = 'fas fa-exclamation-triangle';
            $notification.addClass('warning');
        } else if (type === 'info') {
            iconClass = 'fas fa-info-circle';
            $notification.addClass('info');
        } else {
            $notification.addClass('success');
        }

        $icon.attr('class', iconClass);
        $notification.addClass('show');

        setTimeout(function() {
            $notification.removeClass('show');
        }, 4000);
    }

    /**
     * Update sharing activity list
     */
    function updateSharingActivity(message) {
        var $list = $('#sharingActivity');
        var time = new Date().toLocaleTimeString();

        var $item = $('<li class="activity-item">' +
            message +
            '<div class="activity-time">' + time + '</div>' +
        '</li>');

        // Remove placeholder
        if ($list.find('.no-activity').length > 0) {
            $list.empty();
        }

        $list.prepend($item);

        // Keep only last 10
        if ($list.children().length > 10) {
            $list.children().last().remove();
        }
    }

    /**
     * Start session timer
     */
    function startSessionTimer() {
        sessionStartTime = new Date();

        timerInterval = setInterval(function() {
            var now = new Date();
            var diff = now - sessionStartTime;

            var hours = Math.floor(diff / 3600000);
            var minutes = Math.floor((diff % 3600000) / 60000);
            var seconds = Math.floor((diff % 60000) / 1000);

            var timeStr = pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
            $('#sessionTimer').text(timeStr);
        }, 1000);
    }

    /**
     * Stop session timer
     */
    function stopSessionTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
    }

    /**
     * Pad number with zero
     */
    function pad(num) {
        return num.toString().padStart(2, '0');
    }

    /**
     * End session
     */
    function endSession() {
        if (!confirm('Are you sure you want to end this session? This action cannot be undone.')) {
            return;
        }

        // Save data
        saveFinalScreenSharingData();

        // Stop timer
        stopSessionTimer();

        // Dispose Jitsi
        if (api) {
            try {
                api.dispose();
            } catch (e) {
                console.error('Error disposing API:', e);
            }
        }

        // Submit form
        $('#endSessionForm').submit();
    }

    /**
     * Save recording
     */
    function saveRecording() {
        var recordingUrl = prompt('Please enter the recording URL:');

        if (!recordingUrl) {
            return;
        }

        $.ajax({
            url: config.saveRecordingUrl,
            method: 'POST',
            data: {
                _token: config.csrfToken,
                recording_url: recordingUrl
            },
            success: function(response) {
                showNotification('Recording saved successfully!', 'success');
            },
            error: function(xhr, status, error) {
                console.error('Error saving recording:', error);
                showNotification('Error saving recording. Please try again.', 'error');
            }
        });
    }

    /**
     * Save screen sharing event
     */
    function saveScreenSharingEvent(action, timestamp) {
        screenSharingEvents.push({
            action: action,
            timestamp: timestamp.toISOString(),
            user: config.userName
        });
    }

    /**
     * Save final screen sharing data
     */
    function saveFinalScreenSharingData() {
        if (screenSharingEvents.length === 0) {
            return;
        }

        $.ajax({
            url: config.saveScreenSharingUrl,
            method: 'POST',
            data: {
                _token: config.csrfToken,
                screen_data: screenSharingEvents
            },
            success: function(response) {
                console.log('Screen sharing data saved');
            },
            error: function(xhr, status, error) {
                console.error('Error saving screen sharing data:', error);
            }
        });
    }

})(jQuery);
