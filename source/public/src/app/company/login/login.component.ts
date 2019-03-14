import { Component, OnInit } from '@angular/core';
import { CompanyService } from '../shared/services/company.service';
import { RestfulService } from '../../shared/services/restful.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

  	constructor(private router: Router, private companyService: CompanyService, private resfulService: RestfulService) { }

  	ngOnInit() {
  	}
  	login(form){
  		this.resfulService.doPost("company/login", form).subscribe(commonResponse => this.handleResponse(commonResponse));
  	}
  	private handleResponse(commonResponse: any) {
		if(commonResponse==null) return;
		if(commonResponse.success) {
			this.companyService.save(commonResponse.data.id, commonResponse.data.email,commonResponse.data.api_token, commonResponse.data.username, commonResponse.data.company_id, commonResponse.data.company_name, commonResponse.data.privilege);           
			this.router.navigate(['company/job']);
		}
		else {
			alert(commonResponse.error);
		}
	}
}
