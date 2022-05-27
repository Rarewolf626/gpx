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

    this.alert = function (message, isHtml) {
        isHtml = isHtml === undefined ? true : !!isHtml;
        if (isHtml) {
            this.getContainer().innerHtml = message;
        } else {
            this.getContainer().textContent = message;
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
            console.warn('Could not find content for alert');
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
