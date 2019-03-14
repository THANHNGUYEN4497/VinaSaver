import {Component, OnInit, ViewEncapsulation} from '@angular/core';

import {RestfulService} from '../shared/services/restful.service';
import { AdminService } from './shared/services/admin.service';
import {Router} from "@angular/router";


@Component({
    selector: 'app-admin',
    templateUrl: './admin.component.html',
    styleUrls: ['./admin.component.scss', '../../assets/css/bootstrap.css', '../../assets/css/manage.css'],
    encapsulation: ViewEncapsulation.None
})
export class AdminComponent implements OnInit {
    //currentLink: string;
    username:string;
    id: string;
    constructor(private restfulService: RestfulService, private router: Router, private adminService:AdminService) {
        this.username = this.adminService.getUserName();
        this.id = this.adminService.getId();
    }

    ngOnInit() {

    }

    logout() {
		this.restfulService.doPost("admin/logout",{'admin_id':this.adminService.getId()}).subscribe(commonResponse => this.handleResponse(commonResponse));
	}

	private handleResponse(commonResponse: any) {
		this.adminService.removeAll();
		this.router.navigate(['admin/login']);
	}
}

export const URL_ADMIN_COMPANY_SEARCH       = "admin/company/list";
export const URL_ADMIN_COMPANY_DELETE       = "admin/company/delete/";
export const URL_ADMIN_COMPANY_DETAIL       = "admin/company/detail/";
export const URL_ADMIN_COMPANY_ADD          = "admin/company/add";
export const URL_ADMIN_COMPANY_EDIT         = "admin/company/edit/";
export const URL_ADMIN_COMPANY              = "/admin/company";
export const URL_ADMIN_BUSINESS_LIST        = "admin/business/list";
export const PAGINATION_PER_PAGE            =  10;
export const PAGINATION_INDEX_AMOUNT        =  10;
export const URL_ADMIN_COMPANY_STAFF        =  "admin/company/staff";
export const URL_ADMIN_COMPANY_STAFF_SEARCH =  "admin/staff/list";
export const URL_ADMIN_COMPANY_STAFF_DELETE =  "admin/staff/delete";
export const URL_ADMIN_POSITION_LIST        =  "admin/position/list";
export const URL_ADMIN_COMPANY_STAFF_DETAIL =  "admin/staff/detail/";
export const URL_ADMIN_ACCOUNT_ADD          =  "admin/add";
export const URL_ADMIN_ACCOUNT              =  "admin/account";
export const URL_ADMIN_ACCOUNT_SEARCH       =  "admin/list";
export const URL_ADMIN_ACCOUNT_EDIT         =  "admin/edit/";
export const URL_ADMIN_ACCOUNT_DETAIL       =  "admin/detail/";
export const URL_ADMIN_ACCOUNT_DELETE       =  "admin/delete/";
