import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { Location } from '@angular/common';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

@Component({
    selector: 'app-bonus',
    templateUrl: './bonus.component.html',
    styleUrls: ['./bonus.component.scss']
})
export class BonusComponent implements OnInit {

    constructor(private router: Router, private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService, private location: Location) { }

    ngOnInit() {
        this.getApplicant();
    }

    jobId: number = parseInt(this.route.snapshot.paramMap.get('jobId'));
    applicantId: number = parseInt(this.route.snapshot.paramMap.get('applicantId'));
    applicant: any = {};

    getApplicant() {
        this.restfulService.doGet("company/job/applicant/detail/" + this.applicantId, {}).subscribe(res => {
            if (res.success) {
                if (!res.data || res.data.status < 2) {
                    this.router.navigate(['company/job/' + this.jobId + '/applicant']);
                }
                else {
                    this.applicant = res.data;
                }
            }
            else {
                this.router.navigate(['company/job/' + this.jobId + '/applicant']);
                console.log(res.error);
            }
        });
    }

    bonus(form) {
        form.work_connection_id = this.applicantId;
        this.restfulService.doPost("company/job/applicant/bonus", form).subscribe(res => {
            if (res.success) {
                this.router.navigate(['company/job/' + this.jobId + '/applicant']);    
            }
            else {
                console.log(res.error);
            }
        });
    }
}
