import { Component, OnInit } from '@angular/core';

@Component({
	selector: 'app-share',
	templateUrl: './share.component.html',
	styleUrls: ['./share.component.scss']
})
export class ShareComponent implements OnInit {

	constructor() { }

	ngOnInit() {
		let locationHref = location.href;
		let url = new URL(locationHref);
		let content = url.searchParams.get("content");
		let code = url.searchParams.get("code");
		let jobId = url.searchParams.get("id");

		//Redirect to app store
		if (locationHref.indexOf("content=invi_code") >= 0) {
			//REGISTER scheme
			window.location.href = "locofull://share_content?content=invi_code&code=" + code;
		}
		else {
			//DETAILJOB scheme
			window.location.href = "locofull://share_content?content=job&code=" + code + "&id=" + jobId;
		}
	}
}
