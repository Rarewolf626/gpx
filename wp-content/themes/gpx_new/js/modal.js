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
    }

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
