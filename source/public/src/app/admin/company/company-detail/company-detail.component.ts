import {Component, OnInit} from '@angular/core';
import {ActivatedRoute} from "@angular/router";
import {RestfulService} from "../../../shared/services/restful.service";
import {AdminService} from "../../shared/services/admin.service";
import {environment} from "../../../../environments/environment";
import {URL_ADMIN_BUSINESS_LIST, URL_ADMIN_COMPANY_DETAIL} from "../../admin.component";
import {forEach} from "@angular/router/src/utils/collection";

@Component({
    selector: 'app-company-detail',
    templateUrl: './company-detail.component.html',
    styleUrls: ['./company-detail.component.scss']
})


export class CompanyDetailComponent implements OnInit {

    constructor(private route: ActivatedRoute, private restfulService: RestfulService, private adminService: AdminService) {
    }

    id                      :   number;
    infors                  :   Map<string, string>;
    zoom                    :   number;
    businessFields          :   Map<string, string>;

    ngOnInit() {
        this.id                 =   0;
        this.infors             =   new Map<string, string>();
        this.zoom               =   8;
        this.businessFields     =   new Map<string, string>();
        this.getBusinessFields();
    }

    getBusinessFields() {
        this.restfulService.doGet(URL_ADMIN_BUSINESS_LIST, {}).subscribe(commonResponse => this.onGetBusinessFieldsResponse(commonResponse));
    }

    onGetBusinessFieldsResponse(commonResponse: any) {
        if(commonResponse.success) {
            for (var i = 0; i < commonResponse.data.length; i++) {
                this.businessFields.set(commonResponse.data[i].id.toString(), commonResponse.data[i].business_name);
            }
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
            this.restfulService.doGet(URL_ADMIN_COMPANY_DETAIL + this.id, {}).subscribe(commonResponse => this.onDetailResponse(commonResponse));
        }
    }

    onDetailResponse(commonResponse: any) {
        if (commonResponse.success) {
            for (var key in commonResponse.data)
                this.infors[key] = (key == "image1" || key == "image2" || key == "image3") ? ((commonResponse.data[key]) ? environment.UPLOAD_ENDPOINT + commonResponse.data[key] : null) : commonResponse.data[key];
        }
    }

}
