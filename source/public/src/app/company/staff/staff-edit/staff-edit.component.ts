import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';
import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
    selector: 'app-staff-edit',
    templateUrl: './staff-edit.component.html',
    styleUrls: ['./staff-edit.component.scss']
})
export class StaffEditComponent implements OnInit {

    constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router, private route: ActivatedRoute) { }

    staff: any = {};
    private listPosition: any;
    private tmpPassword = "***************";
    private tmpRePassword = "***************";

    private validatePassword: boolean = true;

    @ViewChild("modal") modal: ModalComponent;
    
    ngOnInit() {
        this.getStaff();
        this.getListPosition();
    }

    getStaff() {
        let staffId = this.route.snapshot.paramMap.get('id');
        this.restfulService.doGet("company/staff/detail/" + staffId, null).subscribe(res => {
            if (res.success) {
                this.staff = res.data;
                this.staff.position = res.data.privilege;
            }
            else {
                alert(res.error);
            }
        });
    }

    validatePasswordInput() {
        if (this.tmpPassword && this.tmpRePassword && (this.tmpPassword != this.tmpRePassword)) {
            this.validatePassword = false;
        }
        else this.validatePassword = true;
    }

    editStaff(form) {
        if ((this.tmpPassword || this.tmpRePassword) && (this.tmpPassword != this.tmpRePassword)) {
            alert('パスワードと確認パスワードが一致しない');
        }
        else {
            if (this.tmpPassword && (this.tmpPassword != "***************")) {
                this.staff.password = this.tmpPassword;
            }
            this.restfulService.doPost('company/staff/edit/' + this.staff.id, this.staff).subscribe(res => {
                if (res.success) {
                    this.modal.toast('修正済み');
                    this.router.navigate(['/company/staff']);
                }
                else {
                    alert(res.error);
                }
            });
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
}
