import { BrowserModule } from '@angular/platform-browser';
import { AppRoutingModule } from './app-routing.module';
import { Routes, RouterModule } from '@angular/router';
import { NgModule } from '@angular/core';
import { Http, HttpModule, RequestOptions, XHRBackend  } from '@angular/http';
import { CookieService } from 'ngx-cookie-service';

import { RestfulService } from './shared/services/restful.service';
import { HttpService } from './shared/services/http.service';

import {FormsModule} from "@angular/forms";
import { MyDatePickerModule } from 'mydatepicker';
import {AgmCoreModule } from '@agm/core';

import { AppComponent } from './app.component';
import { AdminModule } from './admin/admin.module';
import { CompanyModule } from './company/company.module';
import { ShareComponent } from './shared/component/share/share.component';

export function httpFactory(backend: XHRBackend, options: RequestOptions) {
    return new HttpService(backend, options);
}

@NgModule({
  declarations: [
    AppComponent,
    ShareComponent,
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,    
    AdminModule,
    CompanyModule,
    RouterModule,
    HttpModule, 
    FormsModule,
    MyDatePickerModule,
    AgmCoreModule.forRoot({
      apiKey:'AIzaSyAfJTVKnpLl0ULuuwDuix-9ANpyQhP6mfc'
    })
  ],
  providers: [
    RestfulService,
    {   provide: Http,
        useFactory: httpFactory,/*using HttpService */
        deps: [XHRBackend, RequestOptions]
    },
    HttpService, 
    CookieService
  ],
  bootstrap: [AppComponent]
})

export class AppModule { }
