import { Component, OnInit, ViewChild, ElementRef, Output, EventEmitter } from '@angular/core';

@Component({
	selector: 'app-modal',
	templateUrl: './modal.component.html',
	styleUrls: ['./modal.component.scss']
})
export class ModalComponent implements OnInit {

	constructor() { }

	ngOnInit() {
	}

	message: string = "";
	showNotification: boolean = false;

	@ViewChild('open') buttonOpen: ElementRef;
	@Output() confirmEvent = new EventEmitter<boolean>();

	confirm(message: string) {
		this.message = message;
		this.buttonOpen.nativeElement.click();
	}

	timeOut;
	randomId;
	toast(message: string) {
		clearTimeout(this.timeOut);
		let oldTost = document.getElementById(''+this.randomId);
		if (oldTost)	oldTost.remove();
		
		this.message = message;
		this.randomId = Math.random() * (1 - 1000) + 1;
		let e = `
				<div id="${this.randomId}" class="alert alert-info alert-dismissible fade show" role="alert" style="position: fixed; right: 10px; top: 20px; max-width: 500px; z-index: 1000000">
				    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
				    <span aria-hidden="true">&times;</span>
				    </button>
				    ${this.message}
				</div>`;
		document.getElementById("body").insertAdjacentHTML('beforeend', e);
		this.timeOut = setTimeout(() => {
			let eToast = document.getElementById(''+this.randomId);
			if (eToast) eToast.remove();
		}, 4000);
	}

	confirmEventRespone(confirm: boolean) {
		this.confirmEvent.emit(confirm);
	}
}
