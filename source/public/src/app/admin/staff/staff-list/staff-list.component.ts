import {Component, OnInit, ViewChild} from '@angular/core';
import {PaginationComponent} from "../../partials/pagination/pagination.component";
import {ModalComponent} from "../../partials/modal/modal.component";
import {FormBuilder, FormControl, FormGroup, Validators} from "@angular/forms";
import {ActivatedRoute, Router} from "@angular/router";
import {AdminService} from "../../shared/services/admin.service";
import {RestfulService} from "../../../shared/services/restful.service";
import {
    PAGINATION_PER_PAGE, URL_ADMIN_COMPANY_STAFF,
    URL_ADMIN_COMPANY_STAFF_SEARCH, URL_ADMIN_POSITION_LIST
} from "../../admin.component";

@Component({
    selector: 'app-staff-list',
    templateUrl: './staff-list.component.html',
    styleUrls: ['./staff-list.component.scss']
})
export class StaffListComponent implements OnInit {

    @ViewChild( ModalComponent )
    private modalComponent      :   ModalComponent;

    @ViewChild( 'pagination2' )
    private paginationComponent2 :   PaginationComponent;

    @ViewChild( 'pagination' )
    private paginationComponent :   PaginationComponent;

    phoneNumber             :   FormControl;
    keyWord                 :   FormControl;
    companyID               :   FormControl;
    perPage                 :   FormControl;
    page                    :   FormControl;
    position                :   FormControl;
    form                    :   FormGroup;
    positions               :   Map<string, string>;

    constructor(private route: ActivatedRoute, private router: Router, private restfulService: RestfulService, private adminService: AdminService, private formBuilder: FormBuilder) {

    }

    ngOnInit() {
        this.phoneNumber        = new   FormControl("", [Validators.maxLength(15)]);
        this.keyWord            = new   FormControl("", [Validators.maxLength(255)]);
        this.perPage            = new   FormControl(PAGINATION_PER_PAGE, [Validators.maxLength(255)]);
        this.page               = new   FormControl(0, [Validators.maxLength(255)]);
        this.position           = new   FormControl(0, [Validators.maxLength(255)]);
        this.companyID          = new   FormControl("", [Validators.required, Validators.maxLength(15)]);
        this.positions          = new Map<string, string>();
        this.form               = this.formBuilder.group({
            phone_number        : this.phoneNumber,
            keyword             : this.keyWord,
            company_id          : this.companyID,
            per_page            : this.perPage,
            page                : this.page,
            position            : this.position
        });
        this.route
            .queryParams
            .subscribe(params => {
                this.companyID.setValue(params['company_id']);
            });
        this.getPositions();
    }

    getPositions() {
        this.restfulService.doGet(URL_ADMIN_POSITION_LIST, {}).subscribe(commonResponse => this.onGetPositionResponse(commonResponse));
    }

    onGetPositionResponse(commonResponse: any) {
        this.positions.set('', "役職を選択してください。");
        for (var i = 0; i < commonResponse.data.length; i++) {
            this.positions.set(commonResponse.data[i].id, commonResponse.data[i].position_name);
        }
        this.position.setValue('');
        this.search();
    }

    search(num: number = 0) {
        if (this.form.valid) {
            this.page.setValue(((num == 0) ? '' : num));
            this.perPage.setValue(this.paginationComponent.getPerPage());
            this.restfulService.doGet(URL_ADMIN_COMPANY_STAFF_SEARCH, this.form.value).subscribe(commonResponse => this.onSearchResponse(commonResponse));
        }
    }

    onSearchResponse(commonResponse: any) {
        if (commonResponse.success) {
            this.paginationComponent.setData(commonResponse.data);
            this.paginationComponent2.setData(commonResponse.data);
        }
    }

    detail(staffID: number = 0) {
        if (staffID) {
            this.router.navigate([URL_ADMIN_COMPANY_STAFF + "/" + staffID], {queryParams: {company_id: this.companyID.value}});
        }
    }



}
