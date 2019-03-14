import {Component, OnInit, ViewChild} from '@angular/core';
import {FormBuilder, FormControl, FormGroup, Validators} from "@angular/forms";
import {RestfulService} from "../../../shared/services/restful.service";
import {AdminService} from "../../shared/services/admin.service";
import {Router} from "@angular/router";
import {ModalComponent} from "../../partials/modal/modal.component";
import {
    URL_ADMIN_ACCOUNT, URL_ADMIN_ACCOUNT_ADD, URL_ADMIN_COMPANY,
    URL_ADMIN_COMPANY_ADD
} from "../../admin.component";

@Component({
    selector: 'app-account-add',
    templateUrl: './account-add.component.html',
    styleUrls: ['./account-add.component.scss']
})
export class AccountAddComponent implements OnInit {

    @ViewChild( ModalComponent )
    private modalComponent  :   ModalComponent;

    username                :   FormControl;
    password                :   FormControl;
    // passwordConfirmation    :   FormControl;
    email                   :   FormControl;
    phoneNumber             :   FormControl;
    form                    :   FormGroup;
    responseErrors          :   Map<string, string>;

    constructor(private router: Router, private restfulService: RestfulService, private adminService: AdminService, private formBuilder: FormBuilder) {

        this.phoneNumber            = new   FormControl("", [Validators.required, Validators.maxLength(15)]);
        this.username               = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.password               = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        // this.passwordConfirmation   = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.email                  = new   FormControl("", [Validators.required, Validators.maxLength(255), Validators.email]);
        this.form                   = this.formBuilder.group({
            phone_number            : this.phoneNumber,
            email                   : this.email,
            password                : this.password,
            // password_confirmation   : this.passwordConfirmation,
            username                : this.username
        });
        this.password.setValue(this.generateID());
    }

    ngOnInit() {
        this.responseErrors = new Map<string, string>();
    }

    add() {
        if (this.form.valid) {
            this.form.value.admin_id = this.adminService.getId();
            this.restfulService.doPost(URL_ADMIN_ACCOUNT_ADD, this.form.value).subscribe(commonResponse => this.onAddResponse(commonResponse));
        }
        this.form.controls.phone_number.markAsDirty();
        this.form.controls.email.markAsDirty();
        this.form.controls.password.markAsDirty();
        // this.form.controls.password_confirmation.markAsDirty();
        this.form.controls.username.markAsDirty();

    }

    onAddResponse(commonResponse: any) {
        if (commonResponse.success) {
            // this.modalComponent.setBody("企業情報の登録に成功しました！")
            // this.modalComponent.show();
            this.modalComponent.toast("削除しました。");
            this.goToList();
        }
        else {
            delete this.responseErrors;
            this.responseErrors = new Map<string, string>();
            var erros = commonResponse.error;
            for (var key in erros) {
                this.responseErrors[key] = erros[key];
            }
        }
    }

    goToList() {
        this.router.navigate([URL_ADMIN_ACCOUNT]);
    }

    reset() {
        this.form.reset();
    }

    generateID(length: number = 6) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < length; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        return text;
    }


}
