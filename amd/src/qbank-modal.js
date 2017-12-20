define(['jquery', 'core/notification', 'core/custom_interaction_events', 'core/modal', 'core/modal_registry', 'core/modal_events'],
        function($, Notification, CustomEvents, Modal, ModalRegistry, ModalEvents) {

    var registered = false;
    var SELECTORS = {
        ADD_BUTTON: '[data-action="add"]',
        CANCEL_BUTTON: '[data-action="cancel"]',
    };

    /**
     * Constructor for the Modal.
     *
     * @param {object} root The root jQuery element for the modal
     */
    var QbankModal = function(root) {
        Modal.call(this, root);

        if (!this.getFooter().find(SELECTORS.ADD_BUTTON).length) {
            Notification.exception({message: 'No add button found'});
        }

        if (!this.getFooter().find(SELECTORS.CANCEL_BUTTON).length) {
            Notification.exception({message: 'No cancel button found'});
        }


    };

    QbankModal.TYPE = 'mod_automultiplechoice-qbank-modal';
    QbankModal.prototype = Object.create(Modal.prototype);
    QbankModal.prototype.constructor = QbankModal;

    /**
     * Set up all of the event handling for the modal.
     *
     * @method registerEventListeners
     */
    QbankModal.prototype.registerEventListeners = function() {
        // Apply parent event listeners.
        Modal.prototype.registerEventListeners.call(this);

        this.getModal().on(CustomEvents.events.activate, SELECTORS.ADD_BUTTON, function(e, data) {
            var saveEvent = $.Event(ModalEvents.save);
            this.getRoot().trigger(saveEvent, this);

            if (!saveEvent.isDefaultPrevented()) {
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));

        this.getModal().on(CustomEvents.events.activate, SELECTORS.CANCEL_BUTTON, function(e, data) {
            var cancelEvent = $.Event(ModalEvents.cancel);
            this.getRoot().trigger(cancelEvent, this);

            if (!cancelEvent.isDefaultPrevented()) {
                this.hide();
                data.originalEvent.preventDefault();
            }
        }.bind(this));
    };

    // Automatically register with the modal registry the first time this module is imported so that you can create modals
    // of this type using the modal factory.
    if (!registered) {
        ModalRegistry.register(QbankModal.TYPE, QbankModal, 'mod_automultiplechoice/qbank-modal');
        registered = true;
    }

    return QbankModal;
});
