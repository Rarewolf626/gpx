<script setup>
import {ref, watch, onMounted, onUnmounted} from 'vue';
import {storeToRefs} from 'pinia';
import {useCheckoutStore} from "@js/stores/CheckoutStore";

const checkoutStore = useCheckoutStore();
const {hold} = storeToRefs(checkoutStore);
let interval;
let running = ref(false);

const timer = ref({
    days: 0,
    hours: 0,
    minutes: '00',
    seconds: '00'
});

const update = () => {
    if (!hold.value) {
        timer.value = {
            days: 0,
            hours: 0,
            minutes: '00',
            seconds: '00'
        };
        return;
    }

    const distance = hold.value.getTime() - new Date().getTime();

    if (distance < 0) {
        completed();
        return;
    }

    timer.value = {
        days: Math.floor(distance / (1000 * 60 * 60 * 24)),
        hours: Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0'),
        seconds: Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0')
    };
};

const completed = () => {
    timer.value = {
        days: 0,
        hours: 0,
        minutes: '00',
        seconds: '00'
    };
    stopTimer();
};

const startTimer = () => {
    interval = setInterval(update, 1000);
    running.value = true;
}

const stopTimer = () => {
    clearInterval(interval);
    interval = null;
    running.value = false;
}

watch(
    () => hold.value,
    (value) => {
        if (value) {
            if (!running.value) startTimer();
        } else if (running.value) {
            stopTimer();
        }
    }
)

onMounted(() => {
    if (hold.value) {
        update();
        startTimer();
    }
})

onUnmounted(() => {
    if (running.value) {
        stopTimer();
    }
})

</script>

<template>
    <div v-if="hold && running" class="hold-limit-countdown mt-2">

        <div class="hold-limit-countdown-alert">
            ALERT: Limited Availability.
            <span
                v-if="timer.days === 0 && timer.hours === 0">This high-demand week will only be held for 1 hour.</span>
            We recommend completing your booking now.
        </div>

        <div class="show-countdown-timer">
            <div v-show="timer.days > 0">
                <span class="days" v-text="timer.days"/> days
            </div>
            <div v-show="timer.days === 0 && timer.hours > 0">
                <span class="hours" v-text="timer.hours"/>:
            </div>
            <div v-show="timer.days === 0">
                <span class="minutes" v-text="timer.minutes"/>:
            </div>
            <div v-show="timer.days === 0">
                <span class="seconds" v-text="timer.seconds"/>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
