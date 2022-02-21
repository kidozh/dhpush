<!-- Firebase App (the core Firebase SDK) is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.7.1/firebase-app.js"></script>
<!-- Add Firebase messaging -->
<script src="https://www.gstatic.com/firebasejs/8.7.1/firebase-messaging.js"></script>

<!-- register notification if possible-->
<script>
    let self;
    // should render by server
    const form_hash = '<?php echo $formhash ?>';
    var firebaseConfig = {
        apiKey: "AIzaSyA8d6brTx2iU-00YfJrzV24ONoCJFCgMgU",
        authDomain: "discuz-hub.firebaseapp.com",
        projectId: "discuz-hub",
        storageBucket: "discuz-hub.appspot.com",
        messagingSenderId: "7909980581",
        appId: "1:7909980581:web:feb007a7996aa655c5904c",
        measurementId: "G-NLTRJH9BZG"
    };
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();



    function showNotification(payload) {
        Notification.requestPermission(function(status) {
            console.log(status); // 仅当值为 "granted" 时显示通知
            if (status === "granted") {
                const notificationTitle = 'Background Message Title';
                const notificationOptions = {
                    body: 'Background Message body.',
                    icon: '/firebase-logo.png'
                };
                var notification = new Notification(notificationTitle, notificationOptions);
            }

        });

    }

    function sendTokenToServer(token) {
        var postData = new FormData();
        postData.append("formhash", form_hash);
        postData.append("token", token);
        postData.append("deviceName", navigator.userAgent);

        fetch("/plugin.php?id=dhpush:token", {
            method: "POST",
            body: postData
        }).then(res => {
            console.log("Request complete! response:", res);
        });

        // const xhr  = new XMLHttpRequest();
        // xhr.open("POST","/plugin.php?id=dhpush:token", true);
        // xhr.onreadystatechange = function(){
        //     if(this.status == 200){
        //         const data = JSON.parse(this.responseText);
        //         console.log(data);
        //     }
        // }
        // xhr.send(postData);
    }

    function initFirebaseMesssaging() {



        // register service workers
        if ('serviceWorker' in navigator) {


            navigator.serviceWorker.register('./firebase-messaging-sw.js',).then(function(registration) {
                console.log("Service Worker Registered");
                messaging.useServiceWorker(registration);
            });
            // subsequent calls to get tokens from cache
            messaging.getToken({
                vapidKey: "BEA62-1X70ycamkShRQ8KRNjQubtXQlCu_5Y5_9rAo2NtAdMJW_tNWFDNNZWO3vs8NogiqgYr8VTmxpqOX7XgYM"
            }).then((currentToken) => {
                if (currentToken) {
                    // send the token to the server and launch the notification
                    console.log("currentToken " + currentToken);
                    // need to send the token to the server to register or refresh the 
                    sendTokenToServer(currentToken);
                    // then register callback when the page is on the background or foreground
                    // for foreground condition
                    messaging.onMessage((payload) => {
                        console.log("Message received ", payload);
                        showNotification(payload);
                    });

                } else {

                    console.log('No registration token available. Request permission to generate one.');
                }
            }).catch((err) => {
                console.log('An error occurred while retrieving token. ', err);
            });

        }
    }

    // register notification
    window.addEventListener('load', function() {
        // check if notification is needed
        if (window.Notification && Notification.permission !== "granted") {
            Notification.requestPermission(function(status) {
                if (Notification.permission !== status) {
                    Notification.permission = status;
                }

                if (status === "granted") {
                    initFirebaseMesssaging()
                }
            });
        } else if (window.Notification && Notification.permission === "granted") {
            initFirebaseMesssaging()
        }

    });
</script>