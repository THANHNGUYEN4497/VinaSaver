import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';
import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
	selector: 'app-settle',
	templateUrl: './settle.component.html',
	styleUrls: ['./settle.component.scss']
})
export class SettleComponent implements OnInit {
	constructor(private router: Router, private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService) { }

	jobId: number = parseInt(this.route.snapshot.paramMap.get('jobId'));
	applicantId: number = parseInt(this.route.snapshot.paramMap.get('applicantId'));
	currentDay = Date.now();
	applicant: any = {};
	amount: number = 20000;
	content: string = "サービス利用料";

	@ViewChild("modal") modal: ModalComponent;

	ngOnInit() {
		this.getApplicant();
	}

	getApplicant() {
		this.restfulService.doGet("company/job/applicant/detail/" + this.applicantId, {}).subscribe(res => {
			if (res.success) {
				if (!res.data || res.data.status != 3) {
					this.modal.toast("許可されていません");
					this.router.navigate(['company/job/' + this.jobId + '/applicant']);
				}
				else {
					this.applicant = res.data;
				}
			}
			else {
				console.log(res.error);
			}
		});
	}

	settle() {
		let form: any = {};
		form.connector_id = this.applicant.connector_id;
		form.job_id = this.applicant.job_id;
		form.amount = this.amount;
		form.content = this.content;
		this.restfulService.doPost("company/job/applicant/settle", form).subscribe(res => {
			if (res.success) {
				this.modal.toast('終わった');
				this.router.navigate(['company/job/' + this.jobId + '/applicant']);
			}
			else {
				console.log(res.error);
			}
		});
	}
}
