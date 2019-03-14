import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { Location } from '@angular/common';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

@Component({
    selector: 'app-recruit',
    templateUrl: './recruit.component.html',
    styleUrls: ['./recruit.component.scss']
})
export class RecruitComponent implements OnInit {

    jobId: number = parseInt(this.route.snapshot.paramMap.get('jobId'));
    applicantId: number = parseInt(this.route.snapshot.paramMap.get('applicantId'));
    applicant: any = {};

    constructor(private router: Router, private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService, private location: Location) { }

    ngOnInit() {
        this.getApplicant();
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

    recruit(form) {
        form.work_connection_id = this.applicantId;
        form.job_id = this.jobId;
        form.connector_id = this.applicant.connector_id;
        form.introduction_id = this.applicant.introduction_id;
        this.restfulService.doPost("company/job/applicant/recruit", form).subscribe(res => {
            if (res.success) {
                this.router.navigate(['company/job/' + this.jobId + '/applicant']);
            }
            else {
                console.log(res.error);
            }
        });
    }

    ignore() {
        this.restfulService.doPost("company/job/applicant/ignore", { work_connection_id: this.applicantId }).subscribe(res => {
            if (res.success) {
                this.router.navigate(['company/job/' + this.jobId + '/applicant']);
            }
            else {
                console.log(res.error);
            }
        });
    }
}
