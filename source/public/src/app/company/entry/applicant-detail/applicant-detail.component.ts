import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { Location } from '@angular/common';

import { DataShareService, MoveFrom } from '../../shared/services/data-share.service';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

import { CompanyComponent } from '../../company.component';

@Component({
	selector: 'app-applicant-detail',
	templateUrl: './applicant-detail.component.html',
	styleUrls: ['./applicant-detail.component.scss'],
	providers: [CompanyComponent]
})
export class ApplicantDetailComponent implements OnInit {

	jobId: number = parseInt(this.route.snapshot.paramMap.get('jobId'));
	applicantId: number = parseInt(this.route.snapshot.paramMap.get('applicantId'));
	applicant: any = {};

    private movedFrom: MoveFrom;
    private MoveFrom: typeof MoveFrom = MoveFrom;
    private MovedTo: typeof MoveFrom = MoveFrom;
    private movedTo: MoveFrom;

	constructor(private dataShareService: DataShareService, private companyComponent: CompanyComponent, private router: Router, private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService, private location: Location) { }

	ngOnInit() {
        this.movedFrom = this.dataShareService.getMovedFrom();
        switch (this.movedFrom) {
        case MoveFrom.Applicants:
        case MoveFrom.NewApplicants:
            this.applicant = this.dataShareService.getData().applicant;
            this.movedTo = this.dataShareService.getData().moveTo;
            break;
        }
        this.unnew();
		this.getApplicant();
	}
    ngOnDestroy() {
        this.dataShareService.setMovedFrom(MoveFrom.Determine);
    }

	unnew() {
		this.restfulService.doPost("company/job/applicant/unnew/" + this.applicantId, {}).subscribe(res => {
			if (res.success) {

			}
			else {
				console.log(res.error);
			}
		});
	}

	getApplicant() {
		this.restfulService.doGet("company/job/applicant/detail/" + this.applicantId, {}).subscribe(res => {
			if (res.success) {
				this.applicant = res.data;
			}
			else {
				console.log(res.error);
			}
		});
	}
    private moveTo(data) {
        switch (this.movedFrom) {
        case MoveFrom.NewApplicants:
        case MoveFrom.Applicants:
		    this.router.navigate(['company/job/' + data.jobId + '/applicant']);
            break;
        //case MoveFrom.NewApplicants:
		//    this.router.navigate(['company/new-applicants']);
        //    break;
        }
    }

	accept(form) {
		form.work_connection_id = this.applicantId;
		this.restfulService.doPost("company/job/applicant/accept", form).subscribe(res => {
			if (res.success) {
                this.moveTo(this);
			}
			else {
				console.log(res.error);
			}
		});
	}

	ignore() {
		this.restfulService.doPost("company/job/applicant/ignore", { work_connection_id: this.applicantId }).subscribe(res => {
			if (res.success) {
                this.moveTo(this);
			}
			else {
				console.log(res.error);
			}
		});
	}
}
