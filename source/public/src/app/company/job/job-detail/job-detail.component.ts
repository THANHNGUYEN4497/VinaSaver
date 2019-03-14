import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';

import { DataShareService, MoveFrom } from '../../shared/services/data-share.service';
import { environment } from '../../../../environments/environment';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
    selector: 'app-job-detail',
    templateUrl: './job-detail.component.html',
    styleUrls: ['./job-detail.component.scss']
})
export class JobDetailComponent implements OnInit {

    jobImageLink: string = environment.UPLOAD_ENDPOINT + 'job/';
    job: any = {};

    private movedFrom: MoveFrom;
    private MoveFrom: typeof MoveFrom = MoveFrom;

    @ViewChild("modal") modal: ModalComponent;

    constructor(private dataShareService: DataShareService, private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService, private router: Router) { }

    ngOnInit() {
        this.movedFrom = this.dataShareService.getMovedFrom();
        this.getJob();
    }
    ngOnDestroy() {
        this.dataShareService.setMovedFrom(MoveFrom.JobDetail);
    }

    getJob() {
        let jobId = parseInt(this.route.snapshot.paramMap.get('jobId'));
        this.restfulService.doGet("company/job/detail/" + jobId, null).subscribe(res => {
            if (res.success) {
                if (res.data) {
                    this.job = res.data;
                }
            }
            else {
                this.modal.toast(res.error);
                this.router.navigate(['/company/job']);
            }
        });
    }
}
