var isPushEnabled = false;
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': $('meta[name="_token"]').attr('content')
        }
    });

    $('#btnSubmit').on('click', function() {
        $.ajax({
            url : main_url + '&nocache=' + new Date().getTime(),
            type : "post",
            dateType: "JSON",
            data : 'push=1&endpoint=' + endpoint,
            success: function() {
                alert('Push Done !');
            },
            error: function() {
                alert('0');
            }
        });
    });
});


$(window).on('load', function() {
    if (isPushEnabled) {
        unsubscribe();
    } else {
        subscribe();
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(js_url + 'service-worker.js')
        .then(initialiseState);
    } else {
        console.warn('Service workers aren\'t supported in this browser.');
    }
})


function initialiseState() {
    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        console.warn('Notifications aren\'t supported.');
        return;
    }

    if (Notification.permission === 'denied') {
        console.warn('The user has blocked notifications.');
        return;
    }

    if (!('PushManager' in window)) {
        console.warn('Push messaging isn\'t supported.');
        return;
    }

    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        serviceWorkerRegistration.pushManager.getSubscription()
        .then(function(subscription) {
            if (!subscription) {
                return;
            }
            isPushEnabled = true;
            // sendSubscriptionToServer(subscription);
        })
        .catch(function(err) {
            console.warn('Error during getSubscription()', err);
        });
    });
}

function subscribe() {
    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})
        .then(function(subscription) {
            isPushEnabled = true;
            return sendSubscriptionToServer(subscription);
        })
        .catch(function(e) {
            if (Notification.permission === 'denied') {
                console.warn('Permission for Notifications was denied');
            } else {
                console.error('Unable to subscribe to push.', e);
            }
        });
    });
}

function unsubscribe() {
    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        serviceWorkerRegistration.pushManager.getSubscription().then(
            function(pushSubscription) {
                if (!pushSubscription) {
                    isPushEnabled = false;
                    return;
                }

                var subscriptionId = pushSubscription.subscriptionId;
                pushSubscription.unsubscribe().then(function(successful) {
                    isPushEnabled = false;
                }).catch(function(e) {
                    console.log('Unsubscription error: ', e);
                });
            }).catch(function(e) {
                console.error('Error thrown while unsubscribing from push messaging.', e);
            });
        });
}

function sendSubscriptionToServer(subscription) {
    if(!check){
        $.ajax({
            type : 'POST',
            url : main_url + '&nocache=' + new Date().getTime(),
            data : 'save_endpoint=1&endpoint=' + subscription.endpoint,
            success : function(data) {
                //
            }
        });        
    }
}
