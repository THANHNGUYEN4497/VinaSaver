import { Component, OnInit } from '@angular/core';
import {Router, ActivatedRoute, Params} from '@angular/router';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

@Component({
  selector: 'app-credit-list',
  templateUrl: './credit-list.component.html',
  styleUrls: ['./credit-list.component.scss']
})
export class CreditListComponent implements OnInit {

  constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router) { }

  	ngOnInit() {
  		this.getList();
  	}
  	credits: any;

  	getList() {
  		let customerId = this.companyService.getId();
  		this.restfulService.doGet("company/credit-card/list", null).subscribe(res => {
  			if (res.success) {
  				this.credits = res.data;
  				console.log(res.data);
  			}
  			else{
  				alert(res.error);
  			}
  		});
  	}

  	delete(id) {
  		this.restfulService.doPost("company/credit-card/delete/" + id, {}).subscribe(res => {
  			if (res.success) {
  				this.getList();
  			}
  			else{
  				alert(res.error);
  			}
  		});
  	}
}
