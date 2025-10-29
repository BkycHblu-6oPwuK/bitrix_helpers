<template>
    <div id="notify">
        <notifications class="notifier mod-top" position="top center" width="100%" :max="3" group="top"></notifications>
        <notifications class="notifier mod-top" position="top center" width="100%" :max="3" group="header">
        </notifications>
    </div>
</template>

<style scoped>
.notifier {
    z-index: 5
}

.notifier .vue-notification {
    position: relative;
    margin: 0;
    padding: 12px 0;
    border: none
}

.notifier .vue-notification.error {
    background: #facfd7
}

.notifier .vue-notification.error .notification-content {
    color: #c80000
}

.notifier .vue-notification.success {
    background: #d7ecd9
}

.notifier .vue-notification.success .notification-content {
    color: #519f6c
}

.notifier .vue-notification:hover {
    cursor: pointer
}

.notifier .notification-content {
    font-size: 14px;
    letter-spacing: .2px;
    line-height: 21px;
    margin: 0 auto 0 20px;
    max-width: 82%
}

.notifier.mod-top {
    z-index: 15000
}

@media (min-width: 768px) {
    .notifier .vue-notification {
        padding: 12px 0 15px
    }

    .notifier .vue-notification:before {
        right: 24px
    }

    .notifier .notification-content {
        margin: 0 auto;
        text-align: center
    }

    .notifier.mod-header {
        position: absolute;
        z-index: 5;
        margin-top: 118px
    }
}

@media (min-width: 1280px) {
    .notifier .notification-content {
        max-width: 1200px
    }
}
</style>