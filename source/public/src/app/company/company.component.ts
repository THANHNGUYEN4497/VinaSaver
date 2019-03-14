import { Component, OnInit, ViewEncapsulation } from '@angular/core';
import { RestfulService } from '../shared/services/restful.service';
import { CompanyService } from './shared/services/company.service';
import { ChatService } from './shared/services/chat.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-company',
  templateUrl: './company.component.html',
  styleUrls: ['../../assets/css/business.css', './company.component.scss'],
  encapsulation: ViewEncapsulation.None
})
export class CompanyComponent implements OnInit {
	username: string;
	companyName: string;
	total:number = 0;
  	newApplicantNumber: number = 0;
  	privilege: number;
  	
  	constructor(private resfulService: RestfulService, private router: Router, private companyService: CompanyService,  private chatService: ChatService) {}

  	ngOnInit() {
		this.privilege = parseInt(this.companyService.getPrivilege());
		this.chatService.connect();
		this.username = this.companyService.getUserName();
		this.companyName = this.companyService.getCompanyName();
  	}


  	logout(event: Event){
  		event.preventDefault();
  		this. resfulService.doPost('company/logout', {'staff_id': this.companyService.getId()}).subscribe(res => {
  			if (res) {
  				if (res.success) {
					this.companyService.removeAll();
					this.router.navigate(['company/login']);
	  			}
	  			else{
	  				alert(res.error);
	  			}
  			}
  			else alert('Error with response');
  		});
	}

	// reloadCountNew(){
		// let that = this;
		// setInterval(function(){ 
		// 	document.getElementById("progress-loading-style").innerHTML = "<style>#loading_mark{display: none !important;}</style>";
		// 	that.resfulService.doGet('company/chat/total-not-seen', {'company_id': that.companyService.getCompanyId()}).subscribe(res => {
		// 		if (res) {
		// 			if (res.success) {
		// 				that.total = res.data.total_items;
		// 			}
		// 			else{
		// 				alert(res.error);
		// 			}
		// 		}
		// 		else alert('Error with response');
		// 		document.getElementById("progress-loading-style").innerHTML = "";
		// 	});
		// 	that.resfulService.doGet('company/job/applicant/new/count', {}).subscribe(res => {
		// 		if (res.success) {
		// 			that.newApplicantNumber = res.data;
	 //  			}
	 //  			else{
	 //  				console.log(res.error);
	 //  			}
	 //  		});
		// }, 5000);
	// }

}
