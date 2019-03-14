import {Component, OnInit, ViewChild} from '@angular/core';
import {FormBuilder, FormControl, FormGroup, Validators} from "@angular/forms";
import {ActivatedRoute, Router} from "@angular/router";
import {RestfulService} from "../../../shared/services/restful.service";
import {AdminService} from "../../shared/services/admin.service";
import {
    URL_ADMIN_BUSINESS_LIST, URL_ADMIN_COMPANY, URL_ADMIN_COMPANY_DETAIL, URL_ADMIN_COMPANY_EDIT
} from "../../admin.component";
import {ModalComponent} from "../../partials/modal/modal.component";

@Component({
    selector: 'app-company-edit',
    templateUrl: './company-edit.component.html',
    styleUrls: ['./company-edit.component.scss']
})
export class CompanyEditComponent implements OnInit {

    @ViewChild( ModalComponent )
    private modalComponent  : ModalComponent;

    responseErrors          :   Map<string, string>;
    companyName             :   FormControl;
    address                 :   FormControl;
    phoneNumber             :   FormControl;
    business                :   FormControl;
    // url                     : FormControl;
    form                    :   FormGroup;
    id                      :   number;
    email                   :   FormControl;
    businessFields          :   Map<number, string>;

    constructor(private route: ActivatedRoute, private router: Router, private restfulService: RestfulService, private adminService: AdminService, private formBuilder: FormBuilder) {
    }

    ngOnInit() {
        this.companyName            = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.address                = new   FormControl("", [Validators.required, Validators.maxLength(255)]);
        this.phoneNumber            = new   FormControl("", [Validators.maxLength(15)]);
        // this.url                    = new   FormControl("", [Validators.maxLength(255)]);
        this.email                  = new   FormControl("", [Validators.required, Validators.maxLength(255), Validators.email]);
        this.business               = new   FormControl(0, [Validators.required]);
        this.form                   = this.formBuilder.group({
            company_name            : this.companyName,
            address                 : this.address,
            phone_number            : this.phoneNumber,
            // url                     : this.url,
            email                   : this.email,
            business_field          : this.business
        });
        this.responseErrors = new Map<string, string>();
        this.getBusinessFields();
    }

    getBusinessFields() {
        this.restfulService.doGet(URL_ADMIN_BUSINESS_LIST, {}).subscribe(commonResponse => this.onGetBusinessFieldsResponse(commonResponse));
    }

    onGetBusinessFieldsResponse(commonResponse: any) {
        if(commonResponse.success) {
            this.businessFields = commonResponse.data;
            this.route
                .params
                .subscribe(params => {
                    this.get(params['id']);
                });
        }
    }

    edit() {
        if (this.form.valid) {
            this.restfulService.doPost(URL_ADMIN_COMPANY_EDIT + this.id, this.form.value).subscribe(commonResponse => this.onEditResponse(commonResponse));
        }
    }

    onEditResponse(commonResponse: any) {
        if (commonResponse.success) {
            // this.modalComponent.setBody("企業情報の編集に成功しました！");
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

    get(id: number) {
        if (id > 0) {
            this.id = id;
            this.restfulService.doGet(URL_ADMIN_COMPANY_DETAIL + id, {}).subscribe(commonResponse => this.onGetResponse(commonResponse));
        }
    }

    onGetResponse(commonResponse: any) {
        if (commonResponse.success) {
            for (var key in commonResponse.data)
                if (this.form.controls[key]) {
                    if(key == "business_field") {
                        this.form.controls[key].setValue(Number(commonResponse.data[key]));
                    }
                    else
                        this.form.controls[key].setValue(commonResponse.data[key]);
                }


        }
    }

    reset(id: number) {
        this.form.reset();
    }

    goToList() {
        this.router.navigate([URL_ADMIN_COMPANY]);
    }

}
