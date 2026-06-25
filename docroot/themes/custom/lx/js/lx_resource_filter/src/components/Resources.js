class Resources extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {}

  componentWillUnmount() {}

  render() {
    let listItems = this.props.resources.map((resource) => {
      console.log("resource: " + resource);
      return (
        <div className="card card-degree">
          <img
            class="card-img-top"
            src={
              resource.attributes.field_card_image.length !== 0
                ? resource.attributes.field_card_image
                : "/sites/default/files/2021-10/Get_Support_Spotlight.png"
            }
            alt="card image"
          />
          <div class="card-header">
            <h4 class="card-title">{resource.attributes.title}</h4>
          </div>
          <div class="card-body">
            <p class="card-text">
              {resource.attributes.field_card_description.length < 250
                ? resource.attributes.field_card_description
                : resource.attributes.field_card_description.substring(0, 250) +
                  "..."}
            </p>
          </div>
          <div class="card-button">
            <a class="btn btn-dark" href={resource.attributes.path.alias}>
              More info
            </a>
          </div>
        </div>
      );
    });

    return (
      <div>
        {listItems.length > 0 ? (
          <div className="uds-card-arrangement">
            <div className="col-12 uds-card-arrangement-card-container auto-arrangement three-columns">
              {listItems}
            </div>
          </div>
        ) : (
          <div>
            {" "}
            <h3>No Resources Found</h3>{" "}
          </div>
        )}
      </div>
    );
  }
}

  export default Resources;