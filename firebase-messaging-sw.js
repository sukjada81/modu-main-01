importScripts('include/firebase-key-info.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.3/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.6.3/firebase-messaging-compat.js');

firebase.initializeApp({
	apiKey: info_apiKey,
    authDomain: info_authDomain,
    projectId: info_projectId,
    storageBucket: info_storageBucket,
    messagingSenderId: info_messagingSenderId,
    appId: info_appId,
});

const messaging = firebase.messaging();

/*
messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = payload.notification.title;
  const notificationOptions = {
	body: payload.notification.body,
	icon: '/firebase-logo.png'
  };

  self.registration.showNotification(notificationTitle,
	notificationOptions);
});
*/