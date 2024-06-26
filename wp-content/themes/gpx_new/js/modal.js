function Modal(el, options) {
    this.el = el;
    if (Object.prototype.toString.call(el) === "[object String]") {
        // model element is a selector
        this.el = document.querySelector(el) || document.getElementById(el);
    }
    if (!this.el) {
        this.el = document.createElement('dialog');
        if (el) {
            this.el.setAttribute('id', el.replace(/^#/, ''));
        }
        document.body.appendChild(this.el);
    }
    this.native = this.el.tagName === 'DIALOG';
    if (!this.native) {
        if (this.el.parentNode.classList.contains('dialog__overlay')) {
            this.wrapper = this.el.parentNode;
        } else {
            this.wrapper = document.createElement('div');
            this.wrapper.setAttribute('class', 'dialog__overlay');
            this.el.parentNode.appendChild(this.wrapper);
            this.wrapper.appendChild(this.el);
        }
    }

    this.options = Object.assign({
        closeButton: 'closeButton' in this.el.dataset && this.el.dataset.closeButton === 'false' ? false : true,
        closeOnEsc: 'closeOnEsc' in this.el.dataset && this.el.dataset.closeOnEsc === 'false' ? false : true,
        closeOnOutsideClick: 'closeOnOutsideClick' in this.el.dataset && this.el.dataset.closeOnOutsideClick === 'false' ? false : true,
        height: this.el.dataset.height || 'auto',
        minHeight: this.el.dataset.minHeight || null,
        width: this.el.dataset.width || 800,
        parentEl: 'parentEl' in this.el.dataset && this.el.dataset.parentEl ? this.el.dataset.parentEl : null,
        moveToBody: 'moveToBody' in this.el.dataset && this.el.dataset.moveToBody === 'false' ? false : true,
    }, {}, options || {});
    this.closeButton = this.el.querySelector('.dialog__close');
    this.container = this.el.querySelector('.dialog__content');

    this.isOpen = function () {
        if (this.native) {
            return this.el.hasAttribute('open');
        }
        return this.el.classList.contains('open');
    };

    this.open = function () {
        document.body.classList.add('dialog-open');
        if (this.isOpen()) return;
        if (this.native) {
            this.el.showModal();
        } else {
            this.el.classList.add('open');
            this.wrapper.classList.add('open');
        }
    };

    this.close = function () {
        document.body.classList.remove('dialog-open');
        if (!this.isOpen()) return;
        // this.el.classList.add("dialog--hiding");
        if (this.native) {
            this.el.close();
        } else {
            this.el.classList.remove('open');
            this.wrapper.classList.remove('open');
        }

        const event = document.createEvent("Event");
        event.initEvent("closed", true, true);
        this.el.dispatchEvent(event);
    };

    this.destroy = function () {
        if (this.wrapper) {
            this.wrapper.remove();
        } else {
            this.el.remove();
        }
        this.el = null;
        this.container = null;
        this.wrapper = null;
        this.closeButton = null;
    }

    if (this.options.moveToBody && !this.options.parentEl) {
        // move dialog to body to prevent modal issues
        if (this.native && this.el.parentNode !== document.body) {
            document.body.appendChild(this.el);
        }
        if (!this.native && this.wrapper.parentNode !== document.body) {
            document.body.appendChild(this.wrapper);
        }
    }

    if (this.native && window.dialogPolyfill) dialogPolyfill.registerDialog(this.el);
    if (!this.container) {
        this.container = document.createElement('div');
        this.container.setAttribute('class', 'dialog__content');
        let wrapper = document.createElement('div');
        wrapper.setAttribute('class', 'dialog__container');
        wrapper.appendChild(this.container);
        Array.prototype.slice.call(this.el.childNodes).forEach(function (child) {
            this.container.append(child);
        }.bind(this));
        this.el.replaceChildren(wrapper);
    }
    this.el.classList.add('dialog');
    if (this.native) {
        this.el.classList.add('dialog--native');
    }
    if (!isNaN(this.options.width)) {
        this.el.style.maxWidth = this.options.width + 'px';
    } else {
        this.el.style.maxWidth = this.options.width;
    }
    if (!isNaN(this.options.height)) {
        this.container.style.height = this.options.height + 'px';
    } else if (this.options.height.endsWith('%')) {
        this.el.style.height = this.options.height;
    } else if (this.options.height !== 'auto') {
        this.container.style.height = this.options.height;
    }
    if (this.options.minHeight) {
        if (!isNaN(this.options.minHeight)) {
            this.container.style.minHeight = this.options.minHeight + 'px';
        } else if (this.options.minHeight.endsWith('%')) {
            this.el.style.minHeight = this.options.minHeight;
        } else if (!['none', 'auto'].includes(this.options.height)) {
            this.container.style.minHeight = this.options.minHeight;
        }
    }

    this.el.addEventListener('close', function (event) {
        document.body.classList.remove('dialog-open')
    });
    if (this.options.closeButton) {
        if (!this.closeButton) {
            this.closeButton = document.createElement('button');
            this.closeButton.setAttribute('class', 'dialog__close');
            this.closeButton.setAttribute('type', 'reset');
            this.closeButton.setAttribute('aria-label', 'close');
            // this.closeButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
            this.closeButton.innerHTML = '<i class="fa fa-close"></i>';
            this.el.prepend(this.closeButton);
        }
        this.el.classList.add('dialog--has-close-button');
        this.closeButton.addEventListener('click', function () {
            this.close()
        }.bind(this));
    } else {
        this.el.classList.remove('dialog--has-close-button');
        if (this.closeButton) {
            this.closeButton.remove();
            this.closeButton = null;
        }
    }
    if (this.options.closeOnOutsideClick) {
        if (this.native) {
            this.el.addEventListener('click', function (event) {
                if (event.target === this.el) {
                    this.close();
                }
            }.bind(this))
        } else {
            this.wrapper.addEventListener('click', function (event) {
                if (event.target === this.wrapper) {
                    this.close();
                }
            }.bind(this))
        }
    }

    this.el.addEventListener('click', function (event) {
        if (event.target.classList.contains('dialog-close')) {
            event.preventDefault();
            this.close();
        }
    }.bind(this))

    if (this.native) {
        this.el.addEventListener('cancel', function (event) {
            event.preventDefault();
            if (this.options.closeOnEsc) this.close();
        }.bind(this));
    }

    if ('open' in this.el.dataset && this.el.dataset.open !== 'false') {
        this.open();
    }
}

function AlertModal() {

    this.dialog = function () {
        if (this.el) return this.el;
        let el = document.getElementById('alert-dialog');
        if (el) {
            el.classList.add('dialog--alert');
            this.el = el;
            return this.el;
        }
        el = document.createElement('dialog');
        el.setAttribute('id', 'alert-dialog');
        el.setAttribute('class', 'dialog dialog--alert');
        document.body.append(el);
        this.el = el;
        return this.el;
    };

    this.alert = function (message, isHtml, callback) {
        isHtml = isHtml === undefined ? true : !!isHtml;
        if (isHtml) {
            this.getContainer().innerHTML = message;
        } else {
            this.getContainer().textContent = message;
        }
        if (callback) {
            this.modal.el.addEventListener('closed', callback, {once: true});
        }
        this.modal.open();
    };
    this.show = function (template) {
        if (template instanceof jQuery) {
            // this is a jQuery element
            template = template.get(0);
        }
        if (Object.prototype.toString.call(template) === "[object String]") {
            // this is a query selector string
            template = document.getElementById(template) || document.querySelector(template) || document.createElement('div');
        }
        if (template instanceof HTMLElement) {
            // this is a dom node
            this.getContainer().innerHtml = template.innerHTML;
        } else {
            // console.warn('Could not find content for alert');
            this.empty();
        }
        this.modal.open();
    };

    this.getContainer = function () {
        if (!this.content) {
            this.content = this.el.querySelector('.dialog__alert');
        }
        if (!this.content) {
            this.content = document.createElement('div');
            this.content.setAttribute('class', 'dialog__alert');
            this.el.querySelector('.dialog__content').append(this.content);
        }
        return this.content;
    }

    this.empty = function () {
        this.getContainer().innerHTML = '';
    }

    this.modal = new Modal(this.dialog(), {
        width: 460,
        minHeight: 135,
        closeButton: true,
        closeOnOutsideClick: false,
        closeOnEsc: true,
    });
}

function ModalManager() {
    this.modals = [];
    this.add = function (modal) {
        if (modal instanceof Modal) {
            let id = modal.el.id || Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0, 5);
            this.modals.push({
                id: id,
                modal: modal,
            });
            return modal;
        } else if (modal instanceof jQuery) {
            let id = modal.get(0).id || Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0, 5);
            let m = new Modal(modal.get(0));
            this.modals.push({
                id: id,
                modal: m,
            });
            return m;
        } else if (modal instanceof HTMLElement) {
            let id = modal.id || Math.random().toString(36).replace(/[^a-z]+/g, '').substring(0, 5);
            let m = new Modal(modal);
            this.modals.push({
                id: id,
                modal: m,
            });
            return m;
        } else if (Object.prototype.toString.call(modal) === "[object String]") {
            let m = new Modal(modal);
            this.modals.push({
                id: modal,
                modal: m,
            });
            return m;
        }
    };
    this.clear = function () {
        this.modals = [];
    };
    this.get = function (id) {
        for (let i = 0; i < this.modals.length; i++) {
            if (id instanceof Modal) {
                if (this.modals[i].modal === id) {
                    return this.modals[i].modal;
                }
            }
            if (id instanceof jQuery) {
                if (this.modals[i].modal.el === id.get(0)) {
                    return this.modals[i].modal;
                }
            }
            if (id instanceof HTMLElement) {
                if (this.modals[i].modal.el === id) {
                    return this.modals[i].modal;
                }
            }
            if (this.modals[i].id === id) {
                return this.modals[i].modal;
            }
        }
        return null;
    };
    this.closeAll = function () {
        for (let i = 0; i < this.modals.length; i++) {
            this.modals[i].modal.close();
        }
    }
    this.open = function (id) {
        let modal = this.get(id);
        if (!modal) return;
        for (let i = 0; i < this.modals.length; i++) {
            if (id !== this.modals[i].id) {
                this.modals[i].modal.close();
            }
        }
        modal.open();
    }

    this.activate = function (id) {
        if (window.alertModal) {
            window.alertModal.el.close();
        }
        if (Object.prototype.toString.call(id) === "[object String]") {
            id = id.replace(/^#/, '');
        }
        let modal = this.get(id);
        if (modal) {
            this.open(id);
        } else {
            modal = this.add(id);
            this.open(id);
        }
        return modal;
    }.bind(this);

    this.remove = function (id) {
        if (Object.prototype.toString.call(id) === "[object String]") {
            id = id.replace(/^#/, '');
        }
        for (let i = 0; i < this.modals.length; i++) {
            if (this.modals[i].id === id) {
                this.modals.splice(i, 1);
                return;
            }
        }
    }.bind(this);
}
