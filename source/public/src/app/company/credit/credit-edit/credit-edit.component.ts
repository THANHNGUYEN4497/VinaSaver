import { Component, OnInit } from '@angular/core';
import {Router, ActivatedRoute, Params} from '@angular/router';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

@Component({
  selector: 'app-credit-edit',
  templateUrl: './credit-edit.component.html',
  styleUrls: ['./credit-edit.component.scss']
})
export class CreditEditComponent implements OnInit {

  	constructor(private route: ActivatedRoute, private restfulService: RestfulService, private companyService: CompanyService, private router: Router) { }
  	
  	ngOnInit() {
  		this.getCredit();
  	}

  	credit: any = {};

  	getCredit()
  	{
  		let creditId = this.route.snapshot.paramMap.get('id');
  		this.restfulService.doGet("company/credit-card/detail/" + creditId, null).subscribe(res => {
  			if (res.success) {
  				this.credit = res.data;
  			}
  			else{
  				alert(res.error);
  			}
  		})
  	}

  	editCredit() {
  		this.restfulService.doPost("company/credit-card/edit/" + this.credit.id, this.credit).subscribe(res => {
  			if (res.success) {
  				this.router.navigate(['/company/credit']);
  			}
  			else{
  				alert(res.error);
  			}
  		})
  	}
}
