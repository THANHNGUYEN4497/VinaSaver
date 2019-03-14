import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';
import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
	selector: 'app-report',
	templateUrl: './report.component.html',
	styleUrls: ['./report.component.scss']
})
export class ReportComponent implements OnInit {

	jobId: number = parseInt(this.route.snapshot.paramMap.get('jobId'));
	applicantId: number = parseInt(this.route.snapshot.paramMap.get('applicantId'));
	currentDay = Date.now();
	applicant: any = {};
	report_by_company: number = 1;
	amount: number = 20000;
	content: string = "サービス利用料";

    private deleteId: number = 0;

    @ViewChild("modal") modal: ModalComponent;

	constructor(private router: Router, private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService) { }

	ngOnInit() {
		this.getApplicant();
	}


	getApplicant() {
		this.restfulService.doGet("company/job/applicant/detail/" + this.applicantId, {}).subscribe(res => {
			if (res.success) {
				if (!res.data || res.data.status != 2) {
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

	report(form) {
		form.work_connection_id = this.applicantId;
		this.restfulService.doPost("company/job/applicant/report", form).subscribe(res => {
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
