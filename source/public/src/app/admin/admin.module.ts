import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {NgxPaginationModule} from 'ngx-pagination';

import {RestfulService} from '../shared/services/restful.service';
import {HttpService} from '../shared/services/http.service';
import {AdminService} from './shared/services/admin.service';

import {AdminRoutingModule} from './admin-routing.module';
import {AdminAuthGuard} from './shared/admin-auth.guard';

import {AdminComponent} from './admin.component';
import {AccountAddComponent} from './account/account-add/account-add.component';
import {AccountListComponent} from './account/account-list/account-list.component';
import {AccountEditComponent} from './account/account-edit/account-edit.component';
import {CompanyAddComponent} from './company/company-add/company-add.component';
import {CompanyEditComponent} from './company/company-edit/company-edit.component';
import {CompanyDetailComponent} from './company/company-detail/company-detail.component';
import {CompanyListComponent} from './company/company-list/company-list.component';
import {ConnectorDetailComponent} from './connector/connector-detail/connector-detail.component';
import {ConnectorEditComponent} from './connector/connector-edit/connector-edit.component';
import {ConnectorListComponent} from './connector/connector-list/connector-list.component';
import {HistoryPaymentComponent} from './history/history-payment/history-payment.component';
import {HistoryTransferComponent} from './history/history-transfer/history-transfer.component';
import {JobDetailComponent} from './job/job-detail/job-detail.component';
import {JobEditComponent} from './job/job-edit/job-edit.component';
import {JobListComponent} from './job/job-list/job-list.component';
import {StaffDetailComponent} from './staff/staff-detail/staff-detail.component';
import {StaffEditComponent} from './staff/staff-edit/staff-edit.component';
import {StaffListComponent} from './staff/staff-list/staff-list.component';
import {LoginComponent} from "./auth/login/login.component";
import { ApplicantListComponent } from './job/applicant/applicant-list/applicant-list.component';
import { ModalComponent } from './partials/modal/modal.component';
import { PaginationComponent } from './partials/pagination/pagination.component';
import { ApplicantDetailComponent } from './job/applicant-detail/applicant-detail.component';

@NgModule({
    declarations: [AdminComponent, AccountAddComponent, AccountListComponent, AccountEditComponent, CompanyAddComponent, CompanyEditComponent, CompanyDetailComponent, CompanyListComponent, ConnectorDetailComponent, ConnectorEditComponent, ConnectorListComponent, HistoryPaymentComponent, HistoryTransferComponent, JobDetailComponent, JobEditComponent, JobListComponent, StaffDetailComponent, StaffEditComponent, StaffListComponent, LoginComponent, ApplicantListComponent, ModalComponent, PaginationComponent, ApplicantDetailComponent],
    imports: [
        CommonModule,
        AdminRoutingModule,
        FormsModule,
        NgxPaginationModule,
        ReactiveFormsModule,
    ],
    providers: [RestfulService, HttpService, AdminService, AdminAuthGuard]
})
export class AdminModule {
}
