import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';
import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
    selector: 'app-staff-add',
    templateUrl: './staff-add.component.html',
    styleUrls: ['./staff-add.component.scss']
})
export class StaffAddComponent implements OnInit {


    private listPosition: any;
    private validatePassword: boolean = true;
    private cPassword;
    private rePassword;

    @ViewChild("modal") modal: ModalComponent;

    constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router) { }


    ngOnInit() {
        this.getListPosition();
    }

    addStaff(form) {
        if ((form.password || form.re_password) && (form.password != form.re_password)) {
            alert('パスワードと確認パスワードが一致しない');
        }
        else {
            this.restfulService.doPost('company/staff/add', form).subscribe(res => {
                if (res.success) {
                    this.modal.toast('追加しました');
                    this.router.navigate(['/company/staff']);
                }
                else {
                    alert(res.error);
                }
            })
        }
    }

    getListPosition() {
        this.restfulService.doGet('company/job/extend/position', null).subscribe(res => {
            if (res.success) {
                this.listPosition = res.data;
            }
            else {
                alert(res.error);
            }
        });
    }

    validatePasswordInput() {
        if (this.cPassword && this.rePassword && (this.cPassword != this.rePassword)) {
            this.validatePassword = false;
        }
        else this.validatePassword = true;
    }
}
