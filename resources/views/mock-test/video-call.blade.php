<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Test Session - {{ $mockTest->title }}</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src='https://meet.jit.si/external_api.js'></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #f8961e;
            --dark-bg: #1a1a1a;
            --header-bg: #2d2d2d;
            --card-bg: #363636;
            --text-light: #ffffff;
            --text-muted: #b0b0b0;
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--dark-bg);
            color: var(--text-light);
            overflow: hidden;
            height: 100vh;
        }
        
        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--header-bg), #252525);
            padding: 15px 25px;
            border-bottom: 3px solid var(--primary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            position: relative;
        }
        
        .session-info {
            flex: 1;
        }
        
        .session-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .session-title i {
            color: var(--accent-color);
        }
        
        .session-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .meta-item i {
            color: var(--accent-color);
            width: 16px;
        }
        
        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 20px;
            backdrop-filter: blur(10px);
        }
        
        .user-info i {
            color: var(--success-color);
        }
        
        .controls {
            display: flex;
            gap: 12px;
            margin-left: 20px;
        }
        
        .control-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            min-width: 120px;
            justify-content: center;
        }
        
        .control-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .end-btn {
            background: linear-gradient(135deg, var(--danger-color), #d00000);
            color: white;
        }
        
        .save-btn {
            background: linear-gradient(135deg, var(--success-color), #0096c7);
            color: white;
        }
        
        .timer-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-left: 20px;
            backdrop-filter: blur(10px);
        }
        
        .timer {
            font-weight: 600;
            color: var(--text-light);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
        }
        
        #meet {
            width: 100%;
            height: 100%;
            background: #000;
        }
        
        /* Side Panel */
        .side-panel {
            width: 300px;
            background: var(--header-bg);
            border-left: 1px solid #444;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 100;
        }
        
        .side-panel.active {
            transform: translateX(0);
        }
        
        .panel-section {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 15px;
        }
        
        .panel-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .panel-title i {
            color: var(--accent-color);
        }
        
        .activity-list {
            list-style: none;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .activity-item {
            padding: 8px 0;
            border-bottom: 1px solid #444;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-time {
            font-size: 0.75rem;
            color: var(--accent-color);
            margin-top: 2px;
        }
        
        /* Notification */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--success-color), #0096c7);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 10000;
            transform: translateX(150%);
            transition: transform 0.3s ease;
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.error {
            background: linear-gradient(135deg, var(--danger-color), #d00000);
        }
        
        /* Toggle Panel Button */
        .toggle-panel {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 101;
            transition: var(--transition);
        }
        
        .toggle-panel:hover {
            background: var(--secondary-color);
            transform: translateY(-50%) scale(1.1);
        }
        
        /* Loading Screen */
        .loading-screen {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--dark-bg);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            gap: 20px;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #333;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .header {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .controls {
                order: 3;
                width: 100%;
                justify-content: center;
                margin-left: 0;
            }
            
            .session-meta {
                gap: 10px;
            }
            
            .meta-item {
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 768px) {
            .header {
                padding: 12px 15px;
            }
            
            .session-title {
                font-size: 1.1rem;
            }
            
            .control-btn {
                padding: 8px 15px;
                font-size: 0.8rem;
                min-width: 100px;
            }
            
            .user-info, .timer-container {
                margin-left: 10px;
                padding: 6px 12px;
            }
            
            .side-panel {
                width: 100%;
            }
        }
        
        /* Custom Jitsi Styling Overrides */
        .jitsi-container {
            position: relative;
        }
        
        /* Form Styles */
        #endSessionForm {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="session-info">
                <div class="session-title">
                    <i class="fas fa-video"></i>
                    {{ $mockTest->title }}
                </div>
                <div class="session-meta">
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span>Duration: {{ $mockTest->duration_minutes }} minutes</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-door-open"></i>
                        <span>Room: {{ $mockTest->jitsi_room_name }}</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-user-clock"></i>
                        <span>Started: {{ now()->format('M d, Y H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>{{ auth()->user()->name }}</span>
            </div>
            
            <div class="timer-container">
                <i class="fas fa-stopwatch"></i>
                <span class="timer" id="sessionTimer">00:00:00</span>
            </div>
            
            <div class="controls">
                <button id="saveRecording" class="control-btn save-btn">
                    <i class="fas fa-save"></i>
                    Save Recording
                </button>
                <button id="endSession" class="control-btn end-btn">
                    <i class="fas fa-phone-slash"></i>
                    End Session
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Loading Screen -->
            <div class="loading-screen" id="loadingScreen">
                <div class="loading-spinner"></div>
                <p>Connecting to session...</p>
            </div>

            <!-- Jitsi Meet Container -->
            <div id="meet"></div>
            
            <!-- Toggle Panel Button -->
            <button class="toggle-panel" id="togglePanel">
                <i class="fas fa-chevron-left" id="panelIcon"></i>
            </button>
            
            <!-- Side Panel -->
            <div class="side-panel" id="sidePanel">
                <div class="panel-section">
                    <div class="panel-title">
                        <i class="fas fa-share-square"></i>
                        Screen Sharing Activity
                    </div>
                    <ul class="activity-list" id="sharingActivity">
                        <li class="activity-item">No screen sharing activity yet</li>
                    </ul>
                </div>
                
                <div class="panel-section">
                    <div class="panel-title">
                        <i class="fas fa-info-circle"></i>
                        Session Information
                    </div>
                    <div class="activity-item">
                        <strong>Teacher:</strong> {{ $mockTest->teacher->name }}
                    </div>
                    <div class="activity-item">
                        <strong>Student:</strong> {{ $mockTest->student->name }}
                    </div>
                    <div class="activity-item">
                        <strong>Scheduled End:</strong> 
                        {{ $mockTest->scheduled_time->addMinutes($mockTest->duration_minutes)->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification -->
        <div class="notification" id="notification">
            <i class="fas fa-check-circle"></i>
            <span id="notificationText">Operation completed successfully!</span>
        </div>

        <!-- Hidden Form -->
        <form id="endSessionForm" action="{{ route('mock-test.end', $mockTest) }}" method="POST">
            @csrf
        </form>
    </div>

    <script>
        // Configuration
        const domain = 'meet.jit.si';
        const options = {
            roomName: '{{ $mockTest->jitsi_room_name }}',
            width: '100%',
            height: '100%',
            parentNode: document.querySelector('#meet'),
            configOverwrite: {
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                enableWelcomePage: false,
                prejoinPageEnabled: false,
                disableModeratorIndicator: false,
                startScreenSharing: false,
                enableEmailInStats: false,
                enableClosePage: false,
                defaultLanguage: 'en',
                disableThirdPartyRequests: true,
                resolution: 720,
                constraints: {
                    video: {
                        height: { ideal: 720, max: 1080, min: 240 }
                    }
                }
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                    'mute-video-everyone', 'security'
                ],
                SETTINGS_SECTIONS: ['devices', 'language', 'moderator', 'profile', 'calendar'],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                SHOW_BRAND_WATERMARK: false,
                BRAND_WATERMARK_LINK: '',
                SHOW_POWERED_BY: false,
                SHOW_PROMOTIONAL_CLOSE_PAGE: false,
                SHOW_CHROME_EXTENSION_BANNER: false,
                DEFAULT_BACKGROUND: '#1a1a1a',
                VIDEO_QUALITY_LABEL_DISABLED: true
            },
            userInfo: {
                displayName: '{{ Auth::user()->name }}',
                email: '{{ Auth::user()->email }}'
            }
        };

        // Global variables
        let api;
        let screenSharingEvents = [];
        let sessionStartTime = new Date();
        let timerInterval;

        // Initialize Jitsi Meet
        function initializeJitsi() {
            api = new JitsiMeetExternalAPI(domain, options);
            
            // Event listeners
            api.addEventListener('videoConferenceJoined', handleConferenceJoined);
            api.addEventListener('videoConferenceLeft', handleConferenceLeft);
            api.addEventListener('participantJoined', handleParticipantJoined);
            api.addEventListener('participantLeft', handleParticipantLeft);
        }

        // Event handlers
        function handleConferenceJoined() {
            console.log('Successfully joined the conference');
            hideLoadingScreen();
            startSessionTimer();
            showNotification('Successfully joined the session!', 'success');
            
            // Additional event listeners
            api.addEventListener('screenSharingStatusChanged', handleScreenSharing);
            api.addEventListener('recordingStatusChanged', handleRecording);
        }

        function handleConferenceLeft() {
            console.log('Left the conference');
            stopSessionTimer();
            showNotification('You have left the session.', 'info');
            
            // Auto-end session when all participants leave
            setTimeout(() => {
                if (confirm('Session ended. Would you like to save the session data?')) {
                    endSession();
                }
            }, 3000);
        }

        function handleParticipantJoined(event) {
            console.log('Participant joined:', event.displayName);
            showNotification(`${event.displayName} joined the session`, 'info');
        }

        function handleParticipantLeft(event) {
            console.log('Participant left:', event.displayName);
            showNotification(`${event.displayName} left the session`, 'warning');
        }

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

        function handleRecording(event) {
            if (event.on) {
                console.log('Recording started');
                showNotification('Recording started', 'info');
            } else {
                console.log('Recording stopped');
                showNotification('Recording stopped', 'info');
            }
        }

        // UI Functions
        function hideLoadingScreen() {
            document.getElementById('loadingScreen').style.display = 'none';
        }

        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');
            const icon = notification.querySelector('i');
            
            notificationText.textContent = message;
            notification.className = 'notification';
            
            if (type === 'error') {
                notification.classList.add('error');
                icon.className = 'fas fa-exclamation-circle';
            } else if (type === 'warning') {
                icon.className = 'fas fa-exclamation-triangle';
            } else if (type === 'info') {
                icon.className = 'fas fa-info-circle';
            } else {
                icon.className = 'fas fa-check-circle';
            }
            
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 4000);
        }

        function updateSharingActivity(message) {
            const activityList = document.getElementById('sharingActivity');
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            
            const activityItem = document.createElement('li');
            activityItem.className = 'activity-item';
            activityItem.innerHTML = `
                ${message}
                <div class="activity-time">${timeString}</div>
            `;
            
            // Remove placeholder if it exists
            if (activityList.children.length === 1 && 
                activityList.children[0].textContent.includes('No screen sharing')) {
                activityList.innerHTML = '';
            }
            
            activityList.prepend(activityItem);
            
            // Keep only last 10 activities
            if (activityList.children.length > 10) {
                activityList.removeChild(activityList.lastChild);
            }
        }

        function startSessionTimer() {
            timerInterval = setInterval(() => {
                const now = new Date();
                const diff = now - sessionStartTime;
                const hours = Math.floor(diff / 3600000);
                const minutes = Math.floor((diff % 3600000) / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                
                document.getElementById('sessionTimer').textContent = 
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        }

        function stopSessionTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }
        }

        // Control Functions
        function endSession() {
            if (confirm('Are you sure you want to end this session? This action cannot be undone.')) {
                saveFinalScreenSharingData();
                stopSessionTimer();
                document.getElementById('endSessionForm').submit();
            }
        }

        function saveRecording() {
            // In a real implementation, you would get the recording URL from Jitsi
            const recordingUrl = prompt('Please enter the recording URL:');
            if (recordingUrl) {
                $.ajax({
                    url: '{{ route("mock-test.save-recording", $mockTest) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        recording_url: recordingUrl
                    },
                    success: function(response) {
                        showNotification('Recording saved successfully!', 'success');
                    },
                    error: function() {
                        showNotification('Error saving recording. Please try again.', 'error');
                    }
                });
            }
        }

        function saveScreenSharingEvent(action, timestamp) {
            screenSharingEvents.push({
                action: action,
                timestamp: timestamp.toISOString(),
                user: '{{ Auth::user()->name }}'
            });
        }

        function saveFinalScreenSharingData() {
            if (screenSharingEvents.length > 0) {
                $.ajax({
                    url: '{{ route("mock-test.save-screen-sharing", $mockTest) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        screen_data: screenSharingEvents
                    },
                    success: function(response) {
                        console.log('Screen sharing data saved');
                    },
                    error: function() {
                        console.error('Error saving screen sharing data');
                    }
                });
            }
        }

        // Panel Toggle
        document.getElementById('togglePanel').addEventListener('click', function() {
            const panel = document.getElementById('sidePanel');
            const icon = document.getElementById('panelIcon');
            
            panel.classList.toggle('active');
            icon.className = panel.classList.contains('active') ? 
                'fas fa-chevron-right' : 'fas fa-chevron-left';
        });

        // Event Listeners
        document.getElementById('endSession').addEventListener('click', endSession);
        document.getElementById('saveRecording').addEventListener('click', saveRecording);

        // Auto-save screen sharing data every 30 seconds
        setInterval(saveFinalScreenSharingData, 30000);

        // Auto-end session when time is up
        const sessionEndTime = new Date('{{ $mockTest->scheduled_time->addMinutes($mockTest->duration_minutes) }}');
        const timeUntilEnd = sessionEndTime - new Date();
        
        if (timeUntilEnd > 0) {
            setTimeout(() => {
                if (confirm('Session time is up! The session will end automatically.')) {
                    endSession();
                }
            }, timeUntilEnd);
        }

        // Handle page refresh/close
        window.addEventListener('beforeunload', function(e) {
            saveFinalScreenSharingData();
        });

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeJitsi();
        });
    </script>
</body>
</html>