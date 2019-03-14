import {Component, OnInit} from '@angular/core';
import {
    URL_ADMIN_COMPANY_STAFF, URL_ADMIN_COMPANY_STAFF_DETAIL,
    URL_ADMIN_POSITION_LIST
} from "../../admin.component";
import {RestfulService} from "../../../shared/services/restful.service";
import {AdminService} from "../../shared/services/admin.service";
import {ActivatedRoute, Router} from "@angular/router";

@Component({
    selector: 'app-staff-detail',
    templateUrl: './staff-detail.component.html',
    styleUrls: ['./staff-detail.component.scss']
})
export class StaffDetailComponent implements OnInit {

    constructor(private router: Router, private route: ActivatedRoute, private restfulService: RestfulService, private adminService: AdminService) {
    }

    id              :   number;
    infors          :   Map<string, string>;
    positions       :   Map<string, string>;
    companyID       :   number;

    ngOnInit() {
        this.id             =   0;
        this.infors         =   new Map<string, string>();
        this.positions      =   new Map<string, string>();
        this.getPositions();
    }

    getPositions() {
        this.restfulService.doGet(URL_ADMIN_POSITION_LIST, {}).subscribe(commonResponse => this.onGetPositionResponse(commonResponse));
    }

    onGetPositionResponse(commonResponse: any) {
        if (commonResponse.success) {
            for (var i = 0; i < commonResponse.data.length; i++) {
                this.positions.set(commonResponse.data[i].id, commonResponse.data[i].position_name);
            }

            this.route
                .queryParams
                .subscribe(params => {
                    this.companyID = params['company_id'];
                });

            this.route
                .params
                .subscribe(params => {
                    this.id = params['id'];
                    this.detail(this.id);
                });
        }
    }

    detail(id) {
        if (this.id > 0) {
            this.restfulService.doGet(URL_ADMIN_COMPANY_STAFF_DETAIL + this.id, {}).subscribe(commonResponse => this.onDetailResponse(commonResponse));
        }
    }

    onDetailResponse(commonResponse: any) {
        if (commonResponse.success) {
            for (var key in commonResponse.data)
                this.infors[key] = commonResponse.data[key];
        }
    }

    goToList() {
        this.router.navigate([URL_ADMIN_COMPANY_STAFF], {queryParams: {company_id: this.companyID}});
    }
}
