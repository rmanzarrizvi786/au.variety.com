jQuery(document).ready(function ($) {
  if (!window.braze) {
    callBraze();
  }

  // $("body").append(
  //   '<div class="overlay-push-permission"></div><div class="prompt-push-permission-wrap"><div class="prompt-push-permission"><div class="d-flex align-items-start"><div class="icon"><img alt="Variety Australia" src="/wp-content/themes/tbm-var/assets/app/icons/favicon.png"></div><div class="slidedown-body-message">We\'d like to show you notifications for the latest news and updates. </div><div class="clearfix"></div><div id="onesignal-loading-container"></div></div><div class="btns-wrap"><button class="btn-later">Maybe later</button><button class="btn-allow">Allow</button></div></div></div>'
  // );

  $(".overlay-push-permission").on("click", function () {
    $(this).hide();
    $(".prompt-push-permission").hide();
  });

  $(".prompt-push-permission button.btn-later").on("click", function () {
    $(".overlay-push-permission, .prompt-push-permission").hide();
  });
  $(".prompt-push-permission button.btn-allow").on("click", function () {
    $(".overlay-push-permission, .prompt-push-permission").hide();
    // window.braze.requestPushPermission();
  });

  /*
  if (!window.localStorage.getItem("tbm_isOptedOut")) {
    window.localStorage.setItem("tbm_isOptedOut", false);
  }

  if (!window.localStorage.getItem("tbm_isPushNotificationsEnabled")) {
    window.localStorage.setItem("tbm_isPushNotificationsEnabled", false);
  }

  if (
    window.localStorage.getItem("tbm_isPushNotificationsEnabled") == "false" &&
    window.localStorage.getItem("tbm_isOptedOut") != "true"
  ) {
    $("body").append(
      '<div class="overlay-push-permission"></div><div class="prompt-push-permission-wrap"><div class="prompt-push-permission"><div class="d-flex align-items-start"><div class="icon"><img alt="Variety Australia" src="/wp-content/themes/tbm-var/assets/app/icons/favicon.png"></div><div class="slidedown-body-message">We\'d like to show you notifications for the latest news and updates. </div><div class="clearfix"></div><div id="onesignal-loading-container"></div></div><div class="btns-wrap"><button class="btn-later">Maybe later</button><button class="btn-allow">Allow</button></div></div></div>'
    );

    const firebaseConfig = {
      apiKey: "AIzaSyA5HAwwpKxyMDK5qUmO3s6PVTuMPmFST8w",
      authDomain: "the-brag-media-braze.firebaseapp.com",
      projectId: "the-brag-media-braze",
      storageBucket: "the-brag-media-braze.appspot.com",
      messagingSenderId: "1004224111179",
      appId: "1:1004224111179:web:0793e568e758a76b9723ce",
      measurementId: "G-8PB6QBDPP6",
    };
    firebase.initializeApp(firebaseConfig);

    $(".overlay-push-permission").on("click", function () {
      $(this).hide();
      $(".prompt-push-permission").hide();
    });

    $(".prompt-push-permission button.btn-later").on("click", function () {
      $(".overlay-push-permission, .prompt-push-permission").hide();
    });

    const messaging = firebase.messaging();

    $(".prompt-push-permission button.btn-allow").on("click", function () {
      console.clear();

      $(".overlay-push-permission, .prompt-push-permission").hide();
      //Custom function made to run firebase service
      getStartToken();
      //This code recieve message from server /your app and print message to console if same tab is opened as of project in browser
      messaging.onMessage(function (payload) {
        console.log("on Message", payload);
      });

      function getStartToken() {
        messaging
          .getToken()
          .then((currentToken) => {
            console.log(currentToken);
            if (currentToken) {
              sendTokenToServer(currentToken);
            } else {
              // Show permission request.
              RequestPermission();
              setTokenSentToServer(false);
            }
          })
          .catch((err) => {
            setTokenSentToServer(false);
          });
      }

      function RequestPermission() {
        messaging
          .requestPermission()
          .then(function (permission) {
            console.log(permission);
            if (permission === "granted") {
              console.log("have Permission");
              //calls method again and to sent token to server
              getStartToken();
            } else {
              console.log("Permission Denied");
              window.localStorage.setItem("tbm_isOptedOut", true);
            }
          })
          .catch(function (err) {
            console.log(err);
          });
      }

      function sendTokenToServer(token) {
        if (token != false)
          window.localStorage.setItem("tbm_isPushNotificationsEnabled", true);

        if (!isTokensendTokenToServer()) {
          $.ajax({
            url: URL,
            type: "POST",
            data: {
              push_token: token,
            },
            success: function (response) {
              setTokenSentToServer(true);
            },
            error: function (err) {
              setTokenSentToServer(false);
            },
          });
        }
      }

      function isTokensendTokenToServer() {
        return (
          window.localStorage.getItem("tbm_isPushNotificationsEnabled") === true
        );
      }

      function setTokenSentToServer(sent) {
        window.localStorage.setItem("tbm_isPushNotificationsEnabled", sent);
      }
    });
  }
  */
});
