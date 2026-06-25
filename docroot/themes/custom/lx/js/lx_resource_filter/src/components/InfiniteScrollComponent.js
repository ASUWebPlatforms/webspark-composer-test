import React from "react";
import InfiniteScroll from "react-infinite-scroll-component";
import Resource from "./Resource";

class InfiniteScrollComponent extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      offset: 0,
    };
    this.fetchMoreData = this.fetchMoreData.bind(this);
  }

  fetchMoreData() {
    console.log("in fetch more data method");
    let newOffset = this.state.offset + 20;
    console.log("newOffset: " + newOffset);
    this.props.getResources(newOffset);
    this.setState({
      offset: newOffset,
    });
  }

  componentDidMount() {}

  render() {
    return (
      <div>
        <InfiniteScroll
          dataLength={this.props.resources.length}
          next={this.fetchMoreData}
          hasMore={true}
          loader={
            this.props.resources.length > 0 &&
            this.state.offset <= this.props.resources.length ? (
              <h4>Loading...</h4>
            ) : (
              ""
            )
          }
          scrollableTarget="scrollableDiv"
        >
          <div className="uds-card-arrangement">
            <div className="col-12 uds-card-arrangement-card-container auto-arrangement three-columns">
              {this.props.resources.map((i, index) => (
                <Resource data={i} key={index} />
              ))}
            </div>
          </div>
        </InfiniteScroll>
      </div>
    );
  }
}

export default InfiniteScrollComponent;
