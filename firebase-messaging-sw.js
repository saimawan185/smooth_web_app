importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');

firebase.initializeApp({
    apiKey: "ed807bde98d518e5dbd2a9685d72c047ef38ebd8",
    authDomain: "",
    projectId: "smooth-drop-application",
    storageBucket: "smooth-drop-application.firebasestorage.app",
    messagingSenderId: "815952899248",
    appId: "1:815952899248:android:091d77c9049965c145df75",
    measurementId: ""
});

const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    return self.registration.showNotification(payload.data.title, {
        body: payload.data.body ? payload.data.body : '',
        icon: payload.data.icon ? payload.data.icon : ''
    });
});