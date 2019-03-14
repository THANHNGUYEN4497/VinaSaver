import { Injectable } from '@angular/core';
import {CookieService} from 'ngx-cookie-service';
import { UserService } from '../../../shared/services/user.service';

@Injectable()
export class AdminService extends UserService{

	constructor (protected cookieService:CookieService) {
		super();
	}

	public save (id: string,  email: string, token: string, userName: string) {		
		this.cookieService.set("admin_id",id);
		this.cookieService.set("admin_token", token);
		this.cookieService.set("admin_email",email);
		this.cookieService.set("admin_user_name",userName);
	}
	
	public getId() : string {
		return this.cookieService.get("admin_id");
	}
	
	public getEmail() : string {
		return this.cookieService.get("admin_email");
	}
		
	public isAuthenticated() : boolean {
		return !!this.getToken();
	}
	
	public getUserName() : string {
        return this.cookieService.get("admin_user_name")
    }
		
	public getToken() : string {
		return this.cookieService.get("admin_token");
	}
	
	public removeAll() {
		this.cookieService.delete("admin_id");
		this.cookieService.delete("admin_email");
		this.cookieService.delete("admin_user_name");
		this.cookieService.delete("admin_token");
	}
}
