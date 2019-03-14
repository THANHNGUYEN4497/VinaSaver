import { Component, OnInit } from '@angular/core';

@Component({
    selector: 'app-modal',
    templateUrl: './modal.component.html',
    styleUrls: ['./modal.component.scss']
})
export class ModalComponent {

    public visible = false;
    public visibleAnimate = false;
    header: string;
    msg: string;
    msg2: string;
    toast_msg: string = "";
    data: Map<string, string> = new Map<string, string>();

    public show(): void {
        this.visible = true;
        setTimeout(() => this.visibleAnimate = true, 100);
    }

    public hide(): void {
        this.visibleAnimate = false;
        setTimeout(() => this.visible = false, 300);
    }

    public onContainerClicked(event: MouseEvent): void {
        if ((<HTMLElement>event.target).classList.contains('modal')) {
            this.hide();
        }
    }

    public setHeader(headerMsg: string) {
        this.header = headerMsg;
    }

    public setBody(mesage: string) {
        this.msg = mesage;
    }

    public setBody2(mesage: string) {
        this.msg2 = mesage;
    }

    public putData(key, value) {
        this.data[key] = value;
    }

    public getData(key) {
        return this.data[key];
    }

    timeOut;
    randomId;
    toast(message: string) {
        clearTimeout(this.timeOut);
        let oldTost = document.getElementById('' + this.randomId);
        if (oldTost) oldTost.remove();

        this.toast_msg = message;
        this.randomId = Math.random() * (1 - 1000) + 1;
        let e = `
				<div id="${this.randomId}" class="alert alert-info alert-dismissible fade show" role="alert" style="position: absolute; right: 10px; top: 20px; max-width: 500px;">
				    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				    <span aria-hidden="true">&times;</span>
				    </button>
				    ${this.toast_msg}
				</div>`;
        document.getElementById("body").insertAdjacentHTML('beforeend', e);
        this.timeOut = setTimeout(() => {
            let eToast = document.getElementById('' + this.randomId);
            if (eToast) eToast.remove();
        }, 6000);
    }

}
