import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { AdminService } from './services/admin.service';
import { Observable } from 'rxjs';

@Injectable()
export class AdminAuthGuard implements CanActivate {
	constructor(private router: Router, private adminService: AdminService) {}
  	canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    	if (!this.adminService.isAuthenticated()) {
            this.router.navigate(["admin/login"]);
            return false;
        } else {
            return true;
        }
  	}
}
