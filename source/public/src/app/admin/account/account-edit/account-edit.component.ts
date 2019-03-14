import {Component, OnInit, ViewChild} from '@angular/core';
import {ModalComponent} from "../../partials/modal/modal.component";
import {FormBuilder, FormControl, FormGroup, Validators} from "@angular/forms";
import {RestfulService} from "../../../shared/services/restful.service";
import {AdminService} from "../../shared/services/admin.service";
import {ActivatedRoute, Router} from "@angular/router";
import {
    URL_ADMIN_ACCOUNT, URL_ADMIN_ACCOUNT_DETAIL, URL_ADMIN_ACCOUNT_EDIT, URL_ADMIN_COMPANY,
    URL_ADMIN_COMPANY_DETAIL, URL_ADMIN_COMPANY_EDIT
} from "../../admin.component";

@Component({
    selector: 'app-account-edit',
    templateUrl: './account-edit.component.html',
    styleUrls: ['./account-edit.component.scss']
})
export class AccountEditComponent implements OnInit {

    @ViewChild( ModalComponent )
    private modalComponent  :   ModalComponent;

    username                :   FormControl;
    // password                :   FormControl;
    // passwordConfirmation    :   FormControl;
    email                   :   FormControl;
    phoneNumber             :   FormControl;
    form                    :   FormGroup;
    responseErrors          :   Map<string, string>;
    id                      :   number;

    constructor(private route: ActivatedRoute, private router: Router, private restfulService: RestfulService, private adminService: AdminService, private formBuilder: FormBuilder) {
        this.phoneNumber            = new   FormControl("", [Validators.required, Validators.maxLength(15)]);
        this.username               = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        // this.password               = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        // this.passwordConfirmation   = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.email                  = new   FormControl("", [Validators.required, Validators.maxLength(255), Validators.email]);
        this.form                   = this.formBuilder.group({
            phone_number            : this.phoneNumber,
            email                   : this.email,
            // password                : this.password,
            // password_confirmation   : this.passwordConfirmation,
            username                : this.username
        });
    }

    ngOnInit() {
        this.responseErrors = new Map<string, string>();
        this.route
            .params
            .subscribe(params => {
                this.get(params['id']);
            });
    }

    get(id: number) {
        if (id > 0) {
            this.id = id;
            this.restfulService.doGet(URL_ADMIN_ACCOUNT_DETAIL + id, {}).subscribe(commonResponse => this.onGetResponse(commonResponse));
        }
    }

    onGetResponse(commonResponse: any) {
        if (commonResponse.success) {
            for (var key in commonResponse.data)
                if (this.form.controls[key]) {
                    this.form.controls[key].setValue(commonResponse.data[key]);
                }
        }
    }

    goToList() {
        this.router.navigate([URL_ADMIN_ACCOUNT]);
    }

    edit() {
        if (this.form.valid) {
            this.restfulService.doPost(URL_ADMIN_ACCOUNT_EDIT + this.id, this.form.value).subscribe(commonResponse => this.onEditResponse(commonResponse));
        }
    }

    onEditResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.modalComponent.toast("削除しました。");
            this.goToList();
            // this.modalComponent.setBody("企業情報の編集に成功しました！検索ページに戻りたいですか。");
            // this.modalComponent.show();
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
}
