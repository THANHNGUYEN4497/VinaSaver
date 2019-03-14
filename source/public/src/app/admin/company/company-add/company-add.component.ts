import {Component, OnInit, ViewChild} from '@angular/core';
import {FormBuilder, FormControl, FormGroup, Validators} from '@angular/forms';
import {AdminService} from "../../shared/services/admin.service";
import {RestfulService} from "../../../shared/services/restful.service";
import {Router} from "@angular/router";
import {URL_ADMIN_BUSINESS_LIST, URL_ADMIN_COMPANY, URL_ADMIN_COMPANY_ADD} from "../../admin.component";
import {ModalComponent} from "../../partials/modal/modal.component";

@Component({
    selector: 'app-company-add',
    templateUrl: './company-add.component.html',
    styleUrls: ['./company-add.component.scss']
})
export class CompanyAddComponent implements OnInit {

    @ViewChild( ModalComponent )
    private modalComponent  :   ModalComponent;

    responseErrors          : Map<string, string>;
    companyName             : FormControl;
    address                 : FormControl;
    phoneNumber             : FormControl;
    email                   : FormControl;
    emailStaff              : FormControl;
    password                : FormControl;
    passwordConfirmation    : FormControl;
    business                : FormControl;
    form                    : FormGroup;
    usernameStaff           : FormControl;
    businessFields          : Map<number, string>;

    constructor(private router: Router, private restfulService: RestfulService, private adminService: AdminService, private formBuilder: FormBuilder) {

    }

    ngOnInit() {
        this.companyName            = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.address                = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.phoneNumber            = new   FormControl("", [Validators.required, Validators.maxLength(15)]);
        this.email                  = new   FormControl("", [Validators.required, Validators.maxLength(255), Validators.email]);
        this.emailStaff             = new   FormControl("", [Validators.required, Validators.maxLength(255), Validators.email]);
        this.password               = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.passwordConfirmation   = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.business               = new   FormControl(0, [Validators.required]);
        this.usernameStaff          = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.form                   = this.formBuilder.group({
            company_name            : this.companyName,
            address                 : this.address,
            phone_number            : this.phoneNumber,
            email                   : this.email,
            email_staff             : this.emailStaff,
            password                : this.password,
            password_confirmation   : this.passwordConfirmation,
            business_field          : this.business,
            username_staff          : this.usernameStaff
        });
        this.responseErrors         = new Map<string, string>();
        this.getBusinessFields();
    }

    getBusinessFields() {
        this.restfulService.doGet(URL_ADMIN_BUSINESS_LIST, {}).subscribe(commonResponse => this.onGetBusinessFieldsResponse(commonResponse));
    }

    onGetBusinessFieldsResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.businessFields = commonResponse.data;
        }
    }

    add() {
        if (this.form.valid && this.password.value == this.passwordConfirmation.value) {
            this.form.value.admin_id = this.adminService.getId();
            this.restfulService.doPost(URL_ADMIN_COMPANY_ADD, this.form.value).subscribe(commonResponse => this.onAddResponse(commonResponse));
        }
        this.form.controls.company_name.markAsDirty();
        this.form.controls.address.markAsDirty();
        this.form.controls.phone_number.markAsDirty();
        this.form.controls.email.markAsDirty();
        this.form.controls.email_staff.markAsDirty();
        this.form.controls.password.markAsDirty();
        this.form.controls.password_confirmation.markAsDirty();
        this.form.controls.business_field.markAsDirty();
        this.form.controls.username_staff.markAsDirty();
    }

    reset() {
        this.form.reset();
    }

    onAddResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.modalComponent.toast("削除しました。");
            this.goToList();
            // this.modalComponent.setBody("企業情報の登録に成功しました！");
            // this.modalComponent.show();
        }
        else {
            delete this.responseErrors;
            this.responseErrors = new Map<string, string>();
            var erros = commonResponse.error;
            for (var key in erros) {
                console.log(erros);
                this.responseErrors[key] = erros[key];
            }
        }
    }

    goToList() {
        this.router.navigate([URL_ADMIN_COMPANY]);
    }
}
