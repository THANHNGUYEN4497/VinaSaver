package com.unirobot.unibocom.sample;

import android.os.Parcel;
import android.os.Parcelable;

/**
 * Created by HungPhan on 21/03/2018.
 * Copyright © Saver Corp 2018.
 */

public class Member implements Parcelable {
    private String userName;
    private String socketId;
    private String uuid;

    public Member(String userName, String socketId, String uuid) {
        this.userName = userName;
        this.socketId = socketId;
        this.uuid = uuid;
    }

    public String getUserName() {
        return userName;
    }

    public String getSocketId() {
        return socketId;
    }

    public String getUuid() {
        return uuid;
    }

    public static Creator<Member> getCREATOR() {
        return CREATOR;
    }

    protected Member(Parcel in) {
        userName = in.readString();
        socketId = in.readString();
        uuid = in.readString();
    }

    public static final Creator<Member> CREATOR = new Creator<Member>() {
        @Override
        public Member createFromParcel(Parcel in) {
            return new Member(in);
        }

        @Override
        public Member[] newArray(int size) {
            return new Member[size];
        }
    };

    @Override
    public int describeContents() {
        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeString(userName);
        dest.writeString(socketId);
        dest.writeString(uuid);
    }
}
