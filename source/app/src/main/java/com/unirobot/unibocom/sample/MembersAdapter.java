package com.unirobot.unibocom.sample;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;

import com.unirobot.webrtc.unibocom.client.object.User;

import java.util.List;

/**
 * Created by HungPhan on 21/03/2018.
 * Copyright © Saver Corp 2018.
 */

public class MembersAdapter extends RecyclerView.Adapter<MembersAdapter.MemberViewHolder> {
    private Context _context;
    private List<Member> _members;
    private IOnMemberItemListenerClick _iOnMemberItemListenerClick;

    public MembersAdapter(Context context, List<Member> members, IOnMemberItemListenerClick iOnMemberItemListenerClick) {
        _context = context;
        _members = members;
        _iOnMemberItemListenerClick = iOnMemberItemListenerClick;
    }

    @Override
    public MemberViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new MemberViewHolder(LayoutInflater.from(_context).inflate(R.layout.item_user_layout, parent, false));
    }

    @Override
    public void onBindViewHolder(MemberViewHolder holder, int position) {
        final Member member = _members.get(position);
        holder.tvUserNameItem.setText(member.getUserName());
        holder.tvUserSocketItem.setText(member.getSocketId());
        holder.btnAction.setText("SELECT");
        holder.btnAction.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                _iOnMemberItemListenerClick.onCallActionClick(new User(member.getUserName(), member.getSocketId(), member.getUuid()));
            }
        });
    }

    @Override
    public int getItemCount() {
        return _members.size();
    }

    /**
     * User viewholder
     */
    public class MemberViewHolder extends RecyclerView.ViewHolder {
        TextView tvUserNameItem;
        TextView tvUserSocketItem;
        Button btnAction;

        public MemberViewHolder(View itemView) {
            super(itemView);
            tvUserNameItem = (TextView) itemView.findViewById(R.id.tvUserNameItem);
            tvUserSocketItem = (TextView) itemView.findViewById(R.id.tvUserSocketItem);
            btnAction = (Button) itemView.findViewById(R.id.btnAction);
        }
    }

    /**
     * Interface
     */
    public interface IOnMemberItemListenerClick {
        void onCallActionClick(User partner);
    }
}
