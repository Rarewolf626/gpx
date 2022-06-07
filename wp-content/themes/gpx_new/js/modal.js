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
    if (this.el.tagName !== 'DIALOG') {
        throw new Error('Modal element must be a dialog');
    }

    this.options = Object.assign({
        closeButton: 'closeButton' in this.el.dataset && this.el.dataset.closeButton === 'false' ? false : true,
        closeOnEsc: 'closeOnEsc' in this.el.dataset && this.el.dataset.closeOnEsc === 'false' ? false : true,
        closeOnOutsideClick: 'closeOnOutsideClick' in this.el.dataset && this.el.dataset.closeOnOutsideClick === 'false' ? false : true,
        height: this.el.dataset.height || 'auto',
        minHeight: this.el.dataset.minHeight || null,
        width: this.el.dataset.width || 800,
        moveToBody: 'moveToBody' in this.el.dataset && this.el.dataset.moveToBody === 'false' ? false : true,
    }, {}, options || {});
    this.closeButton = this.el.querySelector('.dialog__close');
    this.container = this.el.querySelector('.dialog__content');

    this.open = function () {
        document.body.classList.add('dialog-open');
        this.el.showModal();
    };

    this.close = function () {
        if(!this.el.hasAttribute('open')) return;
        document.body.classList.remove('dialog-open');
        // this.el.classList.add("dialog--hiding");
        this.el.close();
    }

    this.destroy = function () {
        this.el.remove();
        this.el = null;
        this.container = null;
        this.closeButton = null;
    }

    this.animationEnded = function () {
        if (this.el.classList.contains('dialog--hiding')) {
            this.el.close();
            this.el.classList.remove("dialog--hiding");
        }
    };

    if (this.options.moveToBody && this.el.parentNode !== document.body) {
        // move dialog to body to prevent modal issues
        document.body.appendChild(this.el);
    }

    if (window.dialogPolyfill) dialogPolyfill.registerDialog(this.el);
    if (!this.container) {
        this.container = document.createElement('div');
        this.container.setAttribute('class', 'dialog__content');
        Array.prototype.slice.call(this.el.childNodes).forEach(function (child) {
            this.container.append(child);
        }.bind(this));
        this.el.replaceChildren(this.container);
    }
    this.el.classList.add('dialog');
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
    // this.el.addEventListener("animationend", this.animationEnded.bind(this), false);
    if (this.options.closeButton) {
        if (!this.closeButton) {
            this.closeButton = document.createElement('button');
            this.closeButton.setAttribute('class', 'dialog__close');
            this.closeButton.setAttribute('type', 'reset');
            this.closeButton.setAttribute('aria-label', 'close');
            this.closeButton.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24" fill="currentColor"><path d="M0 0h24v24H0z" fill="none"/><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
            if (this.container) {
                this.container.prepend(this.closeButton);
            } else {
                this.el.prepend(this.closeButton);
            }
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
        this.el.addEventListener('click', function (event) {
            if (event.target === this.el) {
                this.close();
            }
        }.bind(this))
    }
    this.el.addEventListener('cancel', function (event) {
        event.preventDefault();
        if (this.options.closeOnEsc) this.close();
    }.bind(this));

    if ('open' in this.el.dataset && this.el.dataset.open !== 'false') {
        this.open();
    }
}
