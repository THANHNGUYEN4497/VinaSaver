import { Injectable } from '@angular/core';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { CompanyService } from './services/company.service';
import { Observable } from 'rxjs';

@Injectable()
export class CompanyAuthGuard implements CanActivate {
	constructor(private router: Router, private companyService: CompanyService) {}
  	canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot) {
    	if (!this.companyService.isAuthenticated()) {
            this.router.navigate(["company/login"]);
            return false;
        } else {
            return true;
        }
  	}
}
