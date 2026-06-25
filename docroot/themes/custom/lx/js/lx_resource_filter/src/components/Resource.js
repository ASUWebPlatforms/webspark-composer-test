import React from "react";

function Resource(props) {
  const resource = props.data.attributes;
  return (
    <div className="card card-degree">
      <img
        className="card-img-top"
        src={
          resource.field_card_image !== 0
            ? resource.field_card_image
            : "/sites/default/files/2021-10/Get_Support_Spotlight.png"
        }
        alt="card"
      />
      <div className="card-header">
        <h4 className="card-title">{resource.title}</h4>
      </div>
      <div className="card-body">
        <p className="card-text">
          {resource.field_card_description < 250
            ? resource.field_card_description
            : resource.field_card_description.substring(0, 250) + "..."}
        </p>
      </div>
      <div className="card-button">
        <a className="btn btn-dark" href={resource.path.alias}>
          More info
        </a>
      </div>
    </div>
  );
}

export default Resource;
