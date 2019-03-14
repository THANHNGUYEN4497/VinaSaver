import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormGroup, Validators } from "@angular/forms";

import { environment } from '../../../../environments/environment';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

@Component({
    selector: 'app-company-info',
    templateUrl: './company-info.component.html',
    styleUrls: ['./company-info.component.scss']
})
export class CompanyInfoComponent implements OnInit {

    company: any = {};
    companyImageUrl: string = environment.UPLOAD_ENDPOINT + 'company/';
    zoom: number;

    private privilege: number;
    
    constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router) {
    }

    ngOnInit() {
        this.privilege = parseInt(this.companyService.getPrivilege());
        this.getCompany();
    }

    getCompany() {
        let companyId = this.companyService.getCompanyId();
        this.restfulService.doGet("company/detail/" + companyId, null).subscribe(res => {
            if (res.success) {
                this.company = res.data;
            }
            else {
                alert(res.error);
            }
        });

        this.zoom = 8;
    }

}
