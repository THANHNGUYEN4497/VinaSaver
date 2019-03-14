import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Router, CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot } from '@angular/router';
import { CompanyService } from './services/company.service';

@Injectable({
	providedIn: 'root'
})
export class CompanyPermissionGuard implements CanActivate {
	constructor(private router: Router, private companyService: CompanyService) {}
  	canActivate(next: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
        if (this.companyService.isAuthenticated() && parseInt(this.companyService.getPrivilege()) == 2) {
        	this.router.navigate(["/company"]);
        	return false;
        }
        return true;
  	}
}
