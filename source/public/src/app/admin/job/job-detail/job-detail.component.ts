import { Component, OnInit } from '@angular/core';
import { RestfulService } from '../../../shared/services/restful.service';
import { ActivatedRoute } from '@angular/router';
import { environment } from '../../../../environments/environment';

@Component({
    selector: 'app-job-detail',
    templateUrl: './job-detail.component.html',
    styleUrls: ['./job-detail.component.scss']
})
export class JobDetailComponent implements OnInit {
    private job_id: number;
    data: any;
    lat: number;
    lng: number;
    zoom: number = 8;
    jobImageLink: string = environment.UPLOAD_ENDPOINT + 'job/';

    constructor(private restfulService: RestfulService, private activeRoute: ActivatedRoute) { }

    ngOnInit() {
        this.activeRoute.params.subscribe(params => {
            this.job_id = params['id'];
        });
        this.getJobInfo();
    }

    getJobInfo() {
        let url = 'admin/job/detail/' + this.job_id;
        this.restfulService.doGet(url, null).subscribe(commonResponse => this.handleResponse(commonResponse));
    }

    private handleResponse(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.data = commonResponse.data;
            this.lat = commonResponse.data.latitude;
            this.lng = commonResponse.data.longitude;
        } else {
            alert(commonResponse.error);
        }
    }
}
