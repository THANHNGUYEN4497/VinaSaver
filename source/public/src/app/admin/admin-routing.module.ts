import {NgModule} from '@angular/core';
import {Routes, RouterModule} from '@angular/router';
import {MyDatePickerModule} from 'mydatepicker';
import {AgmCoreModule } from '@agm/core';

import {AdminAuthGuard} from './shared/admin-auth.guard';

import {AdminComponent} from './admin.component';
import {AccountListComponent} from "./account/account-list/account-list.component";
import {AccountAddComponent} from "./account/account-add/account-add.component";
import {AccountEditComponent} from "./account/account-edit/account-edit.component";
import {CompanyDetailComponent} from "./company/company-detail/company-detail.component";
import {CompanyEditComponent} from "./company/company-edit/company-edit.component";
import {CompanyAddComponent} from "./company/company-add/company-add.component";
import {CompanyListComponent} from "./company/company-list/company-list.component";
import {ConnectorListComponent} from "./connector/connector-list/connector-list.component";
import {ConnectorDetailComponent} from "./connector/connector-detail/connector-detail.component";
import {ConnectorEditComponent} from "./connector/connector-edit/connector-edit.component";
import {HistoryPaymentComponent} from "./history/history-payment/history-payment.component";
import {HistoryTransferComponent} from "./history/history-transfer/history-transfer.component";
import {JobEditComponent} from "./job/job-edit/job-edit.component";
import {JobDetailComponent} from "./job/job-detail/job-detail.component";
import {JobListComponent} from "./job/job-list/job-list.component";
import {StaffListComponent} from "./staff/staff-list/staff-list.component";
import {StaffDetailComponent} from "./staff/staff-detail/staff-detail.component";
import {StaffEditComponent} from "./staff/staff-edit/staff-edit.component";
import {LoginComponent} from "./auth/login/login.component";
import { ApplicantListComponent } from './job/applicant/applicant-list/applicant-list.component';
import {ApplicantDetailComponent} from "./job/applicant-detail/applicant-detail.component";

const routes: Routes = [
    {
        path: 'admin', component: AdminComponent,  canActivate: [AdminAuthGuard],children: [
            { path: '', pathMatch: 'full',redirectTo: 'job'},
            {path: 'account', component: AccountListComponent},
            {path: 'account/add', component: AccountAddComponent},
            {path: 'account/edit/:id', component: AccountEditComponent},
            {path: 'company/staff', component: StaffListComponent},
            {path: 'company/staff/:id', component: StaffDetailComponent},
            {path: 'company/staff/edit/:id', component: StaffEditComponent},
            {path: 'company', component: CompanyListComponent},
            {path: 'company/add', component: CompanyAddComponent},
            {path: 'company/:id', component: CompanyDetailComponent},
            {path: 'company/edit/:id', component: CompanyEditComponent},
            {path: 'connector', component: ConnectorListComponent},
            {path: 'connector/:id', component: ConnectorDetailComponent},
            {path: 'history/payment', component: HistoryPaymentComponent},
            {path: 'history/transfer', component: HistoryTransferComponent},
            {path: 'job', component: JobListComponent},
            {path: 'job/:job_id/applicant', component: ApplicantListComponent},
            {path: 'job/:job_id/applicant/:id', component: ApplicantDetailComponent},
            {path: 'job/:id', component: JobDetailComponent},
            {path: 'job/edit/:id', component: JobEditComponent},
        ]
    },
    {
        path: 'admin/login', component: LoginComponent
    },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule,MyDatePickerModule,AgmCoreModule]
})
export class AdminRoutingModule {
}
