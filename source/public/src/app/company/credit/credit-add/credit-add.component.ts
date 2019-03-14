import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import {FormBuilder, FormGroup, Validators} from "@angular/forms";

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

@Component({
  selector: 'app-credit-add',
  templateUrl: './credit-add.component.html',
  styleUrls: ['./credit-add.component.scss']
})
export class CreditAddComponent implements OnInit {

  constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router) { }

  	ngOnInit() {
  	}

  	addCredit(form)
  	{
  		form.customer_id = this.companyService.getCompanyId();
      form.type = 1;
  		this.restfulService.doPost("company/credit-card/add", form).subscribe(res => {
  			if (res.success) {
  				this.router.navigate(['/company/credit']);
  			}
  			else{
  				alert(res.error);
  			}
  		})
  	}
}
