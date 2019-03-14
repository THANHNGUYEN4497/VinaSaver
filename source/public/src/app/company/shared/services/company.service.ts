import { Injectable } from '@angular/core';
import { CookieService } from 'ngx-cookie-service';
import { UserService } from '../../../shared/services/user.service';

@Injectable()
export class CompanyService extends UserService{

	constructor (protected cookieService:CookieService) {
		super();
	}

	public save (id: string, email: string, token: string, userName: string, companyId: string, companyName:string, privilege: string) {		
		this.cookieService.set("staff_id",id);
		this.cookieService.set("staff_token", token);
		this.cookieService.set("staff_email", email);
		this.cookieService.set("staff_user_name", userName);
		this.cookieService.set("staff_company_id", companyId);
		this.cookieService.set("staff_company_name", companyName);
		this.cookieService.set("staff_privilege", privilege);
	}
	
	public getId() : string {
		return this.cookieService.get("staff_id");
	}
	
	public getEmail() : string {
		return this.cookieService.get("staff_email");
	}
		
	public isAuthenticated() : boolean {
		return !!this.getToken();
	}
	
	public getUserName() : string {
        return this.cookieService.get("staff_user_name")
    }

	public getToken() : string {
		return this.cookieService.get("staff_token");
	}

	public getCompanyId() : string {
		return this.cookieService.get("staff_company_id");
	}

	public getCompanyName() : string {
		return this.cookieService.get("staff_company_name");
	}

	public getPrivilege() : string {
		return this.cookieService.get("staff_privilege");
	}
	
	public removeAll() {
		this.cookieService.delete("staff_id");
		this.cookieService.delete("staff_email");
		this.cookieService.delete("staff_user_name");
		this.cookieService.delete("staff_token");
		this.cookieService.delete("staff_company_id");
		this.cookieService.delete("staff_company_name");
		this.cookieService.delete("staff_privilege");
	}
}
