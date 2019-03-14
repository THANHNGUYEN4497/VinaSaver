import { Component, OnInit } from '@angular/core';
import { RestfulService } from '../../../shared/services/restful.service';
import { AdminService } from '../../shared/services/admin.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['../../../../assets/css/bootstrap.css', '../../../../assets/css/manage.css', './login.component.scss']
})
export class LoginComponent implements OnInit {
  admin: any = {};

  constructor(private router: Router, private restfulService: RestfulService,private adminService: AdminService) { }

  ngOnInit() {

  }

  login() {
		this.restfulService.doPost("admin/login",{'email':this.admin.email,'password':this.admin.password}).subscribe(commonResponse => this.handleResponse(commonResponse));
  }
  
  private handleResponse(commonResponse: any) {
		if(commonResponse==null) return;
		if(commonResponse.success) {	
      this.adminService.save(commonResponse.data.id,commonResponse.data.email,commonResponse.data.api_token, commonResponse.data.username);
      this.router.navigate(['admin']);
		}
		else {
			alert("Login fail!");
		}
	}
}
