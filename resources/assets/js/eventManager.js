export default class EventManager {
    constructor() {
        this.listeners = [];
    }

    on(eventName, listener) {
        if (!this.listeners[eventName]) {
            this.listeners[eventName] = [];
        }

        this.listeners[eventName].push(listener);
    }

    trigger(eventName, ...args) {
        if (!this.listeners[eventName]) {
            return;
        }

        this.listeners[eventName].forEach(function(listener) {
            listener(...args);
        });
    }
}